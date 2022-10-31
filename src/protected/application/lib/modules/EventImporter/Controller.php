<?php

namespace EventImporter;

use DateTime;
use stdClass;
use Exception;
use MapasCulturais\i;
use DateTimeImmutable;
use League\Csv\Reader;
use League\Csv\Writer;
use MapasCulturais\App;
use Shuchkin\SimpleXLS;
use Shuchkin\SimpleXLSX;
use League\Csv\Statement;
use MapasCulturais\Entity;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\MetaList;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use MapasCulturais\Entities\EventOccurrence;

class Controller extends \MapasCulturais\Controller
{
   function GET_downloadExample(){

      $this->requireAuthentication();

      $request = $this->data;
      $dir = PRIVATE_FILES_PATH. "EventImporter";
      $file_name = "event-importer-example";

      if (!is_dir($dir)) {
         mkdir($dir, 0700, true);
      }

      if($request['type'] == 'csv'){
         $this->csvExample($dir, $file_name.".csv");
      }
    
      if($request['type'] == 'xls'){
         $this->xlsExample($dir, $file_name.".xlsx");
      }
   }

   public function xlsExample($dir, $file_name)
   {
      $this->requireAuthentication();

      $app = App::i();
      $moduleConfig = $app->modules['EventImporter']->config;
      $header_example = $moduleConfig['header_example'];
      $path = $dir."/".$file_name;

      $lines[0] = array_keys($header_example);
      foreach($header_example as $key => $values){
         $lines[1][] = $values[0];
         $lines[2][] = $values[1];
      }
     
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->fromArray($lines, null, "A1");

      $writer = new Xlsx($spreadsheet);
      $writer->save($path);
      
      header('Content-Type: application/excel');
      $this->dispatch($file_name, $path);
   }

   public function csvExample($dir, $file_name)
   {
      $app = App::i();
      $moduleConfig = $app->modules['EventImporter']->config;
      $header_example = $moduleConfig['header_example'];

      $path = $dir."/".$file_name;
   
      $stream = fopen($path, 'w');

      $csv = Writer::createFromStream($stream);
      $csv->setDelimiter(",");
      $csv->insertOne(array_keys($header_example));
    
      $csv_data = [];
      foreach($header_example as $key => $values){
         $csv_data[0][] = $values[0];
         $csv_data[1][] = $values[1];
      }

      foreach($csv_data as $data){
         $csv->insertOne($data);
      }
  

      header('Content-Type: application/csv');
      $this->dispatch($file_name, $path);
     
   }

   function GET_processFile()
   {
      $this->requireAuthentication();

      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;
      $enabled = $moduleConfig['enabled'];

      if (!$enabled()) {
         $this->error("Permissão negada, fale com administrador");
      }

      $request = $this->data;
      $file = $app->repo('File')->find($request['file']);
      $file_dir = $file->path;


      if (file_exists($file_dir)) {

         $info = pathinfo($file_dir);
         $ext = $info['extension'];

         switch ($ext) {
            case 'xls':
            case 'xlsx':
               $function = "getDataXLS";
               break;
            case 'csv':
               $function = "getDataCSV";
               break;
         }

         $data = $this->$function($file_dir);
         $this->processData($data, $file_dir);

      } else {
         throw new Exception("Arquivo CSV não existe. Erro ao processar");
      }
   }

   public function getDataCSV($file)
   {
     $stream = fopen($file, 'r');

      $csv = Reader::createFromStream($stream);
      $csv->setDelimiter(",");
      $csv->setHeaderOffset(0);

      $stm = (new Statement());
      $file_data = $stm->process($csv);

      return $file_data;
   }

   public function getDataXLS($file)
   {
      $info = pathinfo($file);
      $ext = $info['extension'];
      $classes = [
         'xls' => \Shuchkin\SimpleXLS::class,
         'xlsx' => \Shuchkin\SimpleXLSX::class,
      ];
      $_class = $classes[$ext] ?? null;

      if(!$_class){
         die("Tipo de arquivo inválido");
      }

      $class = new $_class($file);
      $xls = $class::parseFile($file);
      $rows = $xls->rows();
      $header = $rows[0];

      $file_data = [];
      foreach($rows as $key => $values){
         if($key === 0){continue;}
         
         $file_data[$key] = array_combine($header, $values);
      }

      return $file_data;
   }
   
   public function exampleHash()
   {
      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;

      $header_example = $moduleConfig['header_example'];

      $example = [];
      foreach($header_example as $key => $values){
         $example[0][] = $values[0];
         $example[1][] = $values[1];
      }

      return [
         md5(implode(",", $example[0])),
         md5(implode(",", $example[1])),
      ];
     
   }
   

   //Processa dados do arquivo
   public function processData($file_data, string $file_dir)
   {
      $app = App::i();

      $conn = $app->em->getConnection();

      $moduleConfig = $app->modules['EventImporter']->config;

      $header_default = $moduleConfig['header_default'];

      $header = [];
      foreach($header_default as $hdk => $hdv){
         foreach($hdv as $hk => $hv){

            $header[$hdk][] = $app->slugify($hv);

         }
      }

      $tmp = [];
      $data = [];
      foreach($file_data as $pos => $values){
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

      $exampleHash = $this->exampleHash();

      $errors = [];

      if(empty($data)){
         $errors[0][] = i::__("O arquivo esta vazio, verifique para continuar");
      }

      foreach ($data as $key => $value) {

         $hash = md5(implode(",", $value));
         if(in_array($hash, $exampleHash)){
            $errors[$key+1][] = i::__("Linha invalida. Os dados da linha são os dados do exemplo, apague a mesma para continuar");
            break;
         }

         if(empty($value['NAME']) || $value['NAME'] == ''){
            $errors[$key+1][] = i::__("A coluna nome está vazia");
         }

         if(empty($value['SHORT_DESCRIPTION']) || $value['SHORT_DESCRIPTION'] == ''){
            $errors[$key+1][] = i::__("A coluna descrição curta está vazia");
         }

         if(empty($value['CLASSIFICATION']) || $value['CLASSIFICATION'] == ''){
            $errors[$key+1][] = i::__("A coluna classificação estária está vazia");
         }

         if (!in_array($value['CLASSIFICATION'],$moduleConfig['rating_list_allowed'])) {
            $rating_str = implode(', ',$moduleConfig['rating_list_allowed']);
            $errors[$key+1][] = i::__("A coluna classificação etária é inválida. As opções aceitas são --{$rating_str}--");
         }

         //Validação das linguagens
         $languages = explode(',', $value['LANGUAGE']);
         if (!$languages) {
            $errors[$key+1][] = i::__("A coluna linguagem está vazia");
         }

         //Tratamento da lista
         $languages_list = $app->getRegisteredTaxonomyBySlug('linguagem')->restrictedTerms;

         foreach ($languages as $language) {
            $_language = mb_strtolower(trim($language));

            if (!in_array(trim($_language), array_keys($languages_list))) {
               $errors[$key+1][] = i::__("A linguagem --{$_language}-- não existe");
            }
         }

         // Validação das tags
         $tags = [];
         if($value['TAGS']){
            $tags = explode(',', $value['TAGS']);
         }
 
         //Validação do projeto
         if($value['PROJECT']){
            $collum = $this->checkCollum($value['PROJECT']);
            if(!$projects = $conn->fetchAll("SELECT * FROM project WHERE status >= 1 AND {$collum} = {$value['PROJECT']}")) {
               $errors[$key+1][] = i::__("O projeto não está cadastrado");
            }
   
            if ($collum == 'name') {
               if (count($projects) > 1){
                  $errors[$key+1][] = i::__("Existe mais de um projeto com o nome --{$value['PROJECT']}--. Para proseguir, informe o ID do projeto que quer associar ao evento");
               }
            }
         }

         //Validação do agente responsavel 
         if(empty($value['OWNER']) || ($value['OWNER'] == "")){
            $errors[$key+1][] = i::__("A coluna agente é obrigatória. Informo o ID do agente responsável");
         } else if(!$conn->fetchAll("SELECT * FROM agent WHERE status >= 1 AND id = {$value['OWNER']}")) {
            $errors[$key+1][] = i::__("O a gente não esta cadastrado");
         }
         
         //Caso exista espaço informado significa inserção de ocorrência
         if($value['SPACE'] || $value['FREQUENCY']){
            //Verificação do espaço

            $collum_spa = 'id';
            if (!is_numeric($value['SPACE'])) {
               $collum_spa = 'name';
            }

            $collum = $this->checkCollum($value['SPACE']);
            if(!$spaces = $conn->fetchAll("SELECT * FROM space WHERE status >= 1 AND {$collum} = {$value['SPACE']}")) {
               $errors[$key+1][] = i::__("O espaço não está cadastrado");
            }

            if ($collum_spa == 'name') {
               if (count($spaces) > 1) {
                  $errors[$key+1][] = i::__("Existe mais de um espaço com o nome --{$value['SPACE']}--. Para proseguir informe o ID do espaço que quer associar ao evento");
               }
            }

            //Verificação da frequencia
            if(empty($value['FREQUENCY']) || $value['FREQUENCY'] == ''){
               $errors[$key+1][] = i::__("A coluna frequência está vazia");
            }

            if (!in_array($value['FREQUENCY'], array_keys($moduleConfig['frequence_list_allowed']))) {
               $frequence_str = implode(', ', array_keys($moduleConfig['frequence_list_allowed']));
               $errors[$key+1][] = i::__("A frequência é inválida. As opções aceitas são --{$frequence_str}--");
            }

            if(in_array($value['FREQUENCY'], $moduleConfig['use_week_days'])){
               $err = true;
               foreach(array_keys($moduleConfig['week_days']) as $day){
                  if($value[$day]){
                     $err = false;
                     break;
                  }
               }

               if($err){
                  $errors[$key+1][] = i::__("para a frequencia semanal, pelomenos um dia da semana deve ser informado");
               }
            }

              // Valida a hora inicial
            if(empty($value['STARTS_ON']) || $value['STARTS_ON'] == ''){
               $errors[$key+1][] = i::__("A coluna Hora inícial está vazia");
            }   
            
            $starts_on = $this->formatDate("H:i", $value['STARTS_ON'], false);
            if($starts_on->format("H:i") != $value['STARTS_ON']){
               $errors[$key+1][] = i::__("A coluna Hora inícial é inválida. O formato esperado é HH:MM Ex.: 12:00");
            }
            
            // Valida a hora final
            if(empty($value['ENDS_ON']) || $value['ENDS_ON'] == ''){
               $errors[$key+1][] = i::__("A coluna Hora final está vazia");
            }
            
            $ends_on = $this->formatDate("H:i", $value['ENDS_ON'], false);
            if($ends_on->format("H:i") != $value['ENDS_ON']){
               $errors[$key+1][] = i::__("A coluna Hora final é inválida. O formato esperado é HH:MM Ex.: 12:00");
            }

             // Valida a data inicial
            if (empty($value['STARTS_AT']) || $value['STARTS_AT'] == "") {
               $errors[$key+1][] = i::__("A Coluna Data inícial Está vazia");
            }

            $starts_at = $this->formatDate("d/m/Y", $value['STARTS_AT'], false);
            if ($starts_at->format("d/m/Y") != $value['STARTS_AT']) {
               $errors[$key+1][] = i::__("A coluna data inícial é inválida. O formato esperado é DD/MM/YYYY Ex.: 01/01/2022");
            }
            
            // Valida a data final
            if(in_array($value['FREQUENCY'], $moduleConfig['use_endsat'])){
               if (empty($value['ENDS_AT']) || $value['ENDS_AT'] == "") {
                  $errors[$key+1][] = i::__("A Coluna Data final Está vazia");
               }
               
               $ends_at = $this->formatDate("d/m/Y", $value['ENDS_AT'], false);
               if ($ends_at->format("d/m/Y") != $value['ENDS_AT']) {
                  $errors[$key+1][] = i::__("A coluna data final é inválida. O formato esperado é DD/MM/YYYY Ex.: 01/01/2022");
               }
            }

            if($value['EVENT_ATTENDANCE'] && !is_numeric($value['EVENT_ATTENDANCE'])){
               $errors[$key+1][] = i::__("A coluna total de público é inválida. Essa coluna so aceita números");
            }

         }
         
      }
    
      if($errors){
         $this->render("import-erros", ["errors" => $errors, 'filename' => basename($file_dir)]);
         exit;
      }

      $this->insertEvent($data, $file_dir, $languages, $tags);
   }

   public function checkCollum($value)
   {
      $collum = 'id';
      if (!is_numeric($value)) {
         $collum = 'name';
      }
      return $collum;
   }


   public function insertEvent($data, $file_dir, $languages, $tags)
   {
      $app = App::i();

      foreach ($data as $key => $value) {

         $collum_proj = $this->checkCollum($value['PROJECT']);
         $project = $app->repo("Project")->findOneBy([$collum_proj => $value['PROJECT']]);
         $agent = $app->repo('Agent')->find($value['OWNER']);

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
         $event->longDescription = $value['LONG_DESCRIPTION'];
         $event->classificacaoEtaria = $value['CLASSIFICATION'];
         $event->owner = $agent;
         $event->terms['linguagem'] = $languages;
         $event->projectId = $project ? $project->id : null;
         $event->event_attendance = $value['EVENT_ATTENDANCE'];
         $event->traducaoLibras = $value['LIBRAS_TRANSLATION'];
         $event->descricaoSonora = $value['AUDIO_DESCRIPTION'];
         $event->terms['tag'] = $tags;
         $event->save(true);
     
         if($value['SPACE']){
            $this->createOcurrency($event, $value, $key);
         }
         
         $this->downloadFile($event, $value);
         $this->createMetalists($value, $event);
      }


         $_agent = $app->user->profile;
         $files = json_decode($_agent->event_importer_processed_file) ?? (new stdClass);
         $files->{basename($file_dir)} = date('d/m/Y \à\s H:i');
         $_agent->event_importer_processed_file = json_encode($files);
         $_agent->save(true);
         
         $url = $app->createUrl("painel", "eventos");
         $app->redirect($url."#tab=event-importer");
   }


   public function createOcurrency($event, $value, $key)
   {
      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;

      $freq = mb_strtolower($value['FREQUENCY']);
      $ocurrence = new EventOccurrence();    

      $duration = function() use ($value){
         $start = $this->formatDate("H:i", $value['STARTS_ON']);
         $stop = $this->formatDate("H:i", $value['ENDS_ON']);
         $diferenca = strtotime($stop) - strtotime($start);

         return ($diferenca / 60);
      };

      $collum = $this->checkCollum($value['SPACE']);
      $space = $app->repo("Space")->findOneBy([$collum => $value['SPACE']]);

      $rule = [
         "spaceId" => $space->id,
         "startsAt" => $this->formatDate("d/m/Y", $value['STARTS_AT'], "d/m/Y"),
         "duration" => $duration(),
         "frequency" => $moduleConfig['frequence_list_allowed'][$freq],
         "startsOn" => $this->formatDate("H:i", $value['STARTS_ON'], "H:i"),
         "until" => (!empty($value['ENDS_AT']) && $value['ENDS_AT'] !="")? $this->formatDate("d/m/Y", $value['ENDS_AT'], "Y-m-d") :null,
         "price" => $value['PRICE'],
         "description" => "",
      ];
     
      switch (mb_strtolower($value['FREQUENCY'])) {
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
      $ocurrence->space = $space;
      $ocurrence->separation = 1;
      $ocurrence->timezoneName = 'Etc/UTC';
      $ocurrence->rule = $rule;

      $app->disableAccessControl();
      $ocurrence->save(true);
      $app->enableAccessControl();
   }

   protected function downloadFile(Entity $owner, $value)
   {
      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;
      
      $files_grp_import = $moduleConfig['files_grp_import'];
      
      foreach ($files_grp_import as $key => $grp_import) {
         
         if(!empty($value[$key]) || $value[$key] != ""){
               if($key == "GALLERY" || $key == "DOWNLOADS"){
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
         if($date = DateTime::createFromFormat($formatIn, $date)->format($formatOut)){
            return $date;
         }
         return (new DateTime('1989-01-01'))->format($formatOut);

      }else{
         if($date = DateTime::createFromFormat($formatIn, $date)){
         
            if(date_format($date,'Y-m-d H:i')||$date = date_format($date,'d-m-Y H:i')||$date = date_format($date,'Y/m/d H:i')||$date = date_format($date,'d/m/Y H:i')){
            return $date;
            }
         }
         return (new DateTime('1989-01-01'));
      }
   }

   public function dispatch($file_name, $path)
   {
      header('Content-Disposition: attachment; filename=' . $file_name);
      header('Pragma: no-cache');
      readfile($path);
      unlink($path);
   }
}
