<?php

namespace EventImporter;

use DateTime;
use Exception;
use MapasCulturais\i;
use League\Csv\Reader;
use MapasCulturais\App;
use League\Csv\Statement;
use League\Csv\Writer;
use MapasCulturais\Entity;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\EventOccurrence;
use MapasCulturais\Entities\MetaList;
use stdClass;

class Controller extends \MapasCulturais\Controller
{
   function GET_downloadExample(){

      $this->requireAuthentication();

      $app = App::i();
      $moduleConfig = $app->modules['EventImporter']->config;

      $dir = PRIVATE_FILES_PATH . "EventImporter";
      $file_name = "event-importer-example.csv";

      $path = $dir."/".$file_name;

      if (!is_dir($dir)) {
         mkdir($dir, 0700, true);
      }

      $stream = fopen($path, 'w');

      $csv = Writer::createFromStream($stream);
      $csv->setDelimiter(";");
      $csv->insertOne($moduleConfig['csv_header_example']);

      header('Content-Type: application/csv');
      header('Content-Disposition: attachment; filename=' . $file_name);
      header('Pragma: no-cache');
      readfile($path);
      unlink($path);

   }

   function GET_uploadFile()
   {
      $this->requireAuthentication();

      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;
      $enabled = $moduleConfig['enabled'];

      if(!$enabled()){
         $this->error("Permissão negada, fale com administrador");
      }

      $request = $this->data;
      $file = $app->repo('File')->find($request['file']);
      $file_dir = $file->path;

      if (file_exists($file_dir)) {
         $this->processCSV($file_dir);
      }else{
         throw new Exception("Arquivo CSV não existe. Erro ao processar");
      }
   }

   //Processa arquivos CSV
   public function processCSV(string $file_dir)
   {
      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;

      $stream = fopen($file_dir, 'r');

      $csv = Reader::createFromStream($stream);
      $csv->setDelimiter(";");
      $csv->setHeaderOffset(0);

      $stm = (new Statement());
      $csv_data = $stm->process($csv);
      $header_default = $moduleConfig['header_default'];

      $header = [];
      foreach($header_default as $hdk => $hdv){
         foreach($hdv as $hk => $hv){

            $header[$hdk][] = $app->slugify($hv);

         }
      }

      $tmp = [];
      foreach($csv_data as $pos => $values){
         $collums = array_keys($values);

         foreach($collums as $collum){

            foreach($header as $key => $alloweds){

               $_collum = $app->slugify($collum);
               if(in_array(trim($_collum), $alloweds)){
                  $tmp[$key] = trim($values[$collum]);
               }
              
            }
         }

         $data[$pos] = $tmp;        
      }

      foreach ($data as $key => $value) {
   
         if(empty($value['NAME']) || $value['NAME'] == ''){
            $this->error("A coluna nome está vazia na linha {$key}");
         }

         if(empty($value['SHORT_DESCRIPTION']) || $value['SHORT_DESCRIPTION'] == ''){
            $this->error("A coluna descrição curta está vazia na linha {$key}");
         }

         if(empty($value['CLASSIFICATION']) || $value['CLASSIFICATION'] == ''){
            $this->error("A coluna classificação estária está vazia na linha {$key}");
         }

         if (!in_array($value['CLASSIFICATION'],$moduleConfig['rating_list_allowed'])) {
            $rating_str = implode(', ',$moduleConfig['rating_list_allowed']);
            $this->error("A classificação etária é inválida {$key}. As opções aceitas são --{$rating_str}--");
         }

         //Validação das linguagens
         $languages = explode(',', $value['LANGUAGE']);
         if (!$languages) {
            $this->error("Linguagem está vazia na linha {$key}");
         }

         //Tratamento da lista
         $languages_list = $app->getRegisteredTaxonomyBySlug('linguagem')->restrictedTerms;

         foreach ($languages as $language) {
            $_language = mb_strtolower(trim($language));

            if (!in_array(trim($_language), array_keys($languages_list))) {
               $this->error("A linguagem --{$_language}-- não existe");
            }
         }

         //Validação do projeto
         $collum_proj = 'id';
         if (!is_numeric($value['PROJECT'])) {
            $collum_proj = 'name';
         }

         if (!$projects = $app->repo('Project')->findBy([$collum_proj => $value['PROJECT']])) {
            $this->error("O Projeto Não está cadastrado na linha {$key}");
         }

         if ($collum_proj == 'name') {
            if (count($projects) > 1){
               $this->error("Existem mais de um projeto com o nome {$value['PROJECT']}, Para proseguir informe o ID do projeto que quer associar ao evento");
            }
         }

         //Validação do agente responsavel 
         if (!$agent = $app->repo('Agent')->find($value['OWNER'])) {
            $this->error("O a gente não esta cadastrado");
         }

         //Verificação da frequencia
         if(empty($value['FREQUENCY']) || $value['FREQUENCY'] == ''){
            $this->error("A coluna Frequência está vazia na linha {$key}");
         }

         if (!in_array($value['FREQUENCY'], array_keys($moduleConfig['frequence_list_allowed']))) {
            $frequence_str = implode(', ', array_keys($moduleConfig['frequence_list_allowed']));
            $this->error("A Frequência é inválida na linha {$key}. As opções aceitas são --{$frequence_str}-- ");
         }
         
         //criação do enveto
         $event = new Event();
         $event->name = $value['NAME'];
         $event->subTitle = $value['SUBTITLE'];
         $event->site = $value['SITE'];
         $event->facebook = $value['FACEBOOK'];
         $event->twitter = $value['TWITTER'];
         $event->instagram = $value['INSTAGRAM'];
         $event->youtube = $value['YOUTUBE'];
         $event->linkedin = $value['LINKEDIN'];
         $event->spotify = $value['SPOTIFY'];
         $event->pinterest = $value['PINTEREST'];
         $event->registrationInfo = $value['INSCRICOES'];
         $event->shortDescription = $value['SHORT_DESCRIPTION'];
         $event->classificacaoEtaria = $value['CLASSIFICATION'];
         $event->owner = $agent;
         $event->terms['linguagem'] = $languages;
         $event->projectId = $projects[0]->id;
         $event->save(true);
     
         $this->createOcurrency($event, $value, $key);
         $this->downloadFile($event, $value);
         $this->createMetalists($value, $event);
      }

      $_agent = $app->user->profile;
      $files = json_decode($_agent->event_importer_processed_file) ?? (new stdClass);
      $files->{basename($file_dir)} = date('d/m/Y \à\s H:i');
      $_agent->event_importer_processed_file = json_encode($files);
      $_agent->save(true);
      
      $url = $app->createUrl("painel", "eventos");
      $app->redirect($url);
   }


   public function createOcurrency($event, $value, $key)
   {
      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;

      $collum_spa = 'id';
      if (!is_numeric($value['SPACE'])) {
         $collum_spa = 'name';
      }

      if (!$spaces = $app->repo('Space')->findBy([$collum_spa => $value['SPACE']])) {
         $this->error("O espaço não esta cadastrado");
      }

      if ($collum_spa == 'name') {
         if (count($spaces) > 1) {
            $this->error("Existem mais de um espaço com o nome {$value['SPACE']}, Para proseguir informe o ID do espaço que quer associar ao evento");
         }
      }

      $this->checkFrequency($event, $value, $key);

      $freq = mb_strtolower($value['FREQUENCY']);
      $ocurrence = new EventOccurrence();    

      $duration = function() use ($value){
         $start = $this->formatDate("H:i", $value['STARTS_ON']);
         $stop = $this->formatDate("H:i", $value['ENDS_ON']);
         $diferenca = strtotime($stop) - strtotime($start);

         return ($diferenca / 60);
      };

      $rule = [
         "spaceId" => $spaces[0]->id,
         "startsAt" => $this->formatDate("d/m/Y", $value['STARTS_AT'], "d/m/Y"),
         "duration" => $duration(),
         "frequency" => $moduleConfig['frequence_list_allowed'][$freq],
         "startsOn" => $this->formatDate("H:i", $value['STARTS_ON'], "H:i"),
         "until" => $this->formatDate("d/m/Y", $value['ENDS_AT'], "Y-m-d"),
         "price" => $value['PRICE'],
         "description" => "",
      ];
     
      switch ('semanal') {
         case i::__('diariamente'):
         case i::__('todos os dias'):
         case i::__('diario'):
         case i::__('daily'):
            $exec = function () use (&$ocurrence, $value, $app, &$rule) {

               $ocurrence->endsAt = $this->formatDate("d/m/Y", $value['ENDS_AT'], false);
               $rule['description'].= i::__('Diariamente');

               $months[$value['STARTS_AT']] = $value['STARTS_AT'];
               $months[$value['ENDS_AT']] = $value['ENDS_AT'];
               
               $_months = array_keys($months);
            
               $dateIn = $this->formatDate("d/m/Y", $_months[0], false);
               $dateFn = $this->formatDate("d/m/Y", $_months[1], false);
               
               $years[$dateIn->format("Y")] = $dateIn->format("Y");
               $years[$dateFn->format("Y")] = $dateFn->format("Y");
               $_years = array_keys($years);

               $yearIn = null;
               $yearFn = null;
               if(count($_years) == 1){
                  $yearFn = " de ".$this->formatDate("Y", $_years[0], "Y");
               }else{
                  if(isset($_years[0]) && isset($_years[0])){
                     $yearIn = " de ".$this->formatDate("Y", $_years[0], "Y");
                     $yearFn = " de ".$this->formatDate("Y", $_years[1], "Y");
                  }else{
                     $yearFn = " de ".$this->formatDate("Y", $_years[0], "Y");
                  }
               }
              
               $start = $this->formatDate("H:i", $value['STARTS_ON'], false);
               if(count($_months) == 1){
                  $dateFn = $this->formatDate("d/m/Y", $_months[1], false);
                  $rule['description'].= " de {$dateIn->format("d")} a {$dateFn->format("d")} de  {$dateIn->format("F")} {$yearIn}  às {$start->format("H:i")}";
               }else{
                  $rule['description'].= " de {$dateIn->format("d")} de {$dateIn->format("F")} {$yearIn} a {$dateFn->format("d")} de {$dateFn->format("F")} {$yearFn} às {$start->format("H:i")}";
               }
            };
            break;
         case i::__('semanal'):
         case i::__('toda semana'):
         case i::__('weekly'):
            $exec = function () use ($ocurrence, $value, $app, &$rule) {

               $ocurrence->endsAt = $this->formatDate("d/m/Y", $value['ENDS_AT'], false);

               $moduleConfig = $app->modules['EventImporter']->config;

               $week_days = array_keys($moduleConfig['week_days']);
               $days_list_positive = $moduleConfig['days_list_positive'];

               $days = [];
               foreach ($week_days as $key => $day) {
                  if (in_array($value[$day], $days_list_positive)) {
                     $days[$key] = "on";
                  }
               }

               $rule['endsAt'] = $this->formatDate("d/m/Y", $value['ENDS_AT'], "d/m/Y");
               $rule['day'] = $days;

               $count = count($days);
               $d = array_values($moduleConfig['week_days']);
               
               if($days){
                  $rule['description'] = "Toda ";
                  foreach($days as $key => $day){
            
                     if($count == 1){
                        $rule['description'].= i::__(' e ');
                     }
                 
                     $rule['description'].= $d[$key];
            
                     if($count > 2){
                        $rule['description'].= i::__(', ');
                     }
   
                     $count--;
                  }

                  $rule['description'].= i::__(' ');
               }
              
               $months[$value['STARTS_AT']] = $value['STARTS_AT'];
               $months[$value['ENDS_AT']] = $value['ENDS_AT'];

               $_months = array_keys($months);

               $dateIn = $this->formatDate("d/m/Y", $_months[0], false);
               $dateFn = $this->formatDate("d/m/Y", $_months[1], false);
             
               $years[$dateIn->format("Y")] = $dateIn->format("Y");
               $years[$dateFn->format("Y")] = $dateFn->format("Y");
               $_years = array_keys($years);

               $yearIn = null;
               $yearFn = null;
               if(count($_years) == 1){
                  $yearFn = i::__(" de ".$this->formatDate("Y", $_years[0], "Y"));
               }else{
                  if(isset($_years[0]) && isset($_years[0])){
                     $yearIn = i::__(" de ".$this->formatDate("Y", $_years[0], "Y"));
                     $yearFn = i::__(" de ".$this->formatDate("Y", $_years[1], "Y"));
                  }else{
                     $yearFn = i::__(" de ".$this->formatDate("Y", $_years[0], "Y"));
                  }
               }
               
               $start = $this->formatDate("H:i", $value['STARTS_ON'], false);
               if(count($_months) == 1){
                  $rule['description'].= i::__("de {$dateIn->format("d")} a {$dateFn->format("d")} de  {$dateIn->format("F")} {$yearFn}  às {$start->format("H:i")}");
               }else{
                  $dateFn = $this->formatDate("d/m/Y", $_months[1], false);
                  $rule['description'].= i::__("de {$dateIn->format("d")} de {$dateIn->format("F")} {$yearIn} a {$dateFn->format("d")} de {$dateFn->format("F")} {$yearFn} às {$start->format("H:i")}");
               }
            };
            break;
         case i::__('uma vez'):
         case i::__('once'):
            $exec = function () use ($ocurrence, $value, $app, &$rule) {
               $dateIn = $this->formatDate("d/m/Y", $value['STARTS_AT'], false);
               $start = $this->formatDate("H:i", $value['STARTS_ON'], false);

               $rule['description'].= i::__("Dia {$dateIn->format("d")} de {$dateIn->format("F")} de {$dateIn->format("Y")} às {$start->format("H:i")}");
            };
            break;
      }

      $exec();
      
      $from = array_keys($moduleConfig['dic_months']);
      $to = array_values($moduleConfig['dic_months']);
      $rule['description'] = str_replace($from, $to, $rule['description']);
      
      $ocurrence->startsOn = $this->formatDate("H:i", $value['STARTS_ON'], false);
      $ocurrence->endsOn = $this->formatDate("H:i", $value['ENDS_ON'], false);
      $ocurrence->startAt = $this->formatDate("d/m/Y", $value['STARTS_AT'], false);
      $ocurrence->frequency = $moduleConfig['frequence_list_allowed'][$freq];
      $ocurrence->status = EventOccurrence::STATUS_ENABLED;
      $ocurrence->event = $event;
      $ocurrence->space = $spaces[0];
      $ocurrence->separation = 1;
      $ocurrence->timezoneName = 'Etc/UTC';
      $ocurrence->rule = $rule;

      $app->disableAccessControl();
      $ocurrence->save(true);
      $app->enableAccessControl();
   }

   public function checkFrequency($event, $value, $key)
   {
      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;

      // Valida a hora inicial
      $starts_on = $this->formatDate("d/m/Y H:i", ($value['STARTS_AT']." ".$value['STARTS_ON']), false);
      if(empty($value['STARTS_ON']) || $value['STARTS_ON'] == ''){
         $this->error("A coluna Hora inícial está vazia na linha {$key}");
      }   

      if($starts_on->format("H:i") != $value['STARTS_ON']){
         $this->error("A coluna Hora final é inválida na linha {$key}");
      }
      
      // Valida a hora final
      $ends_on = $this->formatDate("d/m/Y H:i", ($value['ENDS_AT']." ".$value['ENDS_ON']), false);
      if(empty($value['ENDS_ON']) || $value['ENDS_ON'] == ''){
         $this->error("A coluna Hora final está vazia na linha {$key}");
      }
      
      if($ends_on->format("H:i") != $value['ENDS_ON']){
         $this->error("A coluna Hora final é inválida na linha {$key}");
      }
      
    
      // Valida a data inicial
      $starts_at = $this->formatDate("d/m/Y", $value['STARTS_AT'], false);
      if ($starts_at->format("d/m/Y") != $value['STARTS_AT']) {
         $this->error("O formato da Data inícial é inválido na linha {$key}. O formato esperado é YYYY/MM/DD");
      }
      
      if (empty($value['STARTS_AT']) || $value['STARTS_AT'] == "") {
         $this->error("A Coluna Data inícial Está vazia na linha {$key}");
      }
      
      // Valida a data final
      if(in_array($value['FREQUENCY'], $moduleConfig['use_endsat'])){
         $ends_at = $this->formatDate("d/m/Y", $value['ENDS_AT'], false);
         if (empty($value['ENDS_AT']) || $value['ENDS_AT'] == "") {
            $this->error("A Coluna Data Final Está vazia na linha {$key}");
         }
   
         if ($ends_at->format("d/m/Y") != $value['ENDS_AT']) {
            $this->error("O formato da Data Final é inválido na linha {$key}. O formato esperado é YYYY/MM/DD");
         }
      }
   }

   protected function downloadFile(Entity $owner, $value)
   {
      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;
      
      $files_grp_import = $moduleConfig['files_grp_import'];
      
      foreach ($files_grp_import as $key => $grp_import) {
         
         if(!empty($value[$key]) || $value[$key] != ""){
            if($key == "GALLERY"){
               $gallery_list = $this->matches($value[$key]);
   
               foreach($gallery_list as $item){
                  $this->saveFile($item, $owner, $grp_import);
               }
            }else{
               $this->saveFile($value[$key], $owner, $grp_import);
            }
         }
      }
   }

   public function saveFile($value, $owner, $grp_import)
   {

      $exp = explode(":", $value);

      $_file = $exp[0].":".$exp[1];
      $description = isset($exp[2]) ? $exp[2] : null;

      $basename = basename($_file);
      $file_data = str_replace($basename, urlencode($basename), $_file);

      $ch = curl_init($file_data);
      $tmp = tempnam("/tmp", "");
      $handle = fopen($tmp, "wb");
     
      if (!$this->urlFileExists($_file)) {
         fclose($handle);
         unlink($tmp);
         return false;
      }
 
      curl_setopt($ch, CURLOPT_FILE, $handle);

      if (!curl_exec($ch)) {
         fclose($handle);
         unlink($tmp);
         return false;
      }

      curl_close($ch);
      $sz = ftell($handle);
      fclose($handle);

      $class_name = $owner->fileClassName;

      $file = new $class_name([
         "name" => $basename,
         "type" => mime_content_type($tmp),
         "tmp_name" => $tmp,
         "error" => 0,
         "size" => filesize($tmp)
      ]);

      $file->group = $grp_import;
      $file->owner = $owner;
      $file->description = $description;
      $file->save(true);
   }

   public function createMetalists($value, Entity $owner)
   {
      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;
      
      $metalists_import = $moduleConfig['metalists_import'];
      foreach($metalists_import as $key => $metalist){
         $lists = $this->matches($value[$metalist]);
         foreach($lists as $item){
            $exp = explode(":", $item);

            $url = $exp[0].":".$exp[1];
            $title = isset($exp[2]) ? $exp[2] : null;

            $metaList = new MetaList();
            $metaList->owner = $owner;
            $group = (strpos($url, 'youtube') > 0 || strpos($url, 'youtu.be') > 0 || strpos($url, 'vimeo') > 0) ? 'videos' : 'links';
            $metaList->group = $group;
            $metaList->title = $title ?? "" ;
            $metaList->value = $url ?? "";
            $metaList->save(true);
         }
      }
   }

   function urlFileExists($url) {

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_NOBODY, true);
      curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
  
      return ($code == 200);
  }

   public function matches($value)
   {
      preg_match_all("#\[(.+?)\]#", $value, $matches);
      return $matches[1];
   }

   public function error($message)
   {
      throw new Exception(i::__($message));
   }

   public function formatDate($formatIn, $date, $formatOut = "Y-m-d H:i")
   {
      if($formatOut){
         return DateTime::createFromFormat($formatIn, $date)->format($formatOut);
      }
     
      return DateTime::createFromFormat($formatIn, $date);
   }
  
}
