<?php

namespace EventImporter;

use DateTime;
use stdClass;
use Curl\Curl;
use Exception;
use MapasCulturais\i;
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
      ini_set('max_execution_time', 0);
      ini_set('memory_limit', '768M');
      
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
         $this->processData($data, $file);

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

   public function typeProcessMap($value)
   {
      $options = [
         "create_event" => function() use($value){
            return (empty($value['EVENT_ID'])) ? true : false;
         },
         "edit_event" => function() use($value){
            if($value["EVENT_ID"]){
               unset($value["EVENT_ID"]);
               if(!empty(array_filter($value))){
                  return true;
               }
            }

            return false;
         },
         "delete_event" => function() use($value){
            if($value["EVENT_ID"]){
               unset($value["EVENT_ID"]);
               if(empty(array_filter($value))){
                  return true;
               }
            }

            return false;
         },
         "create_ocurrence" => function() use($value){
            return (empty($value['EVENT_ID']) && !empty($value['SPACE'])) ? true : false;
         },
         "edit_ocurrence" => function() use($value){
            return (!empty($value['EVENT_ID']) && !empty($value['SPACE'])) ? true : false;
         },
      ];

      $result = [];
      foreach($options as $key => $option){
         $result[$key] = $option();
      }
      
    
     return (object)$result;

   }
   public function ApplySeal($event,$value){

      $app = App::i();
      
      if($value["SEAL_ID"]){
         $app->disableAccessControl();
         $relations = $event->getSealRelations();
         $seal = $app->repo('Seal')->find($value["SEAL_ID"]);
         
         $has_seal = false;
         foreach($relations as $relation){
            if($relation->seal->id == $seal->id){
               $has_seal = true;
               break;
            }
         }
         
         if(!$has_seal){
            $event->createSealRelation($seal);
         }
         $app->enableAccessControl();
       
      }
   }

   public function processData($file_data, $file)
   {
      $app = App::i();

      $file_dir = $file->path;

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

         if(!empty(array_filter($tmp))){
            $data[$pos] = $tmp;        
         }
      }

      $exampleHash = $this->exampleHash();

      $errors = [];

      if(empty($data)){
         $errors[0][] = i::__("O arquivo esta vazio, verifique para continuar");
      }
     

      $not_found = array_diff(array_keys($moduleConfig['header_default']), array_keys($data[1]));
      if(!empty($not_found)){
         $coll = [];
         foreach($not_found as $collum){
            $coll[] = mb_strtoupper($moduleConfig['header_default'][$collum][0]);
         }
         $_coll = implode(", ", $coll);
         $errors[1][] = i::__("As colunas {$_coll} não foram encontradas na planilha");
      }

      $clearOcurrenceList = [];
      if(empty($not_found)){
         foreach ($data as $key => $value) {

            if(empty(array_filter($value))){
               continue;
            }

            $type_process_map = $this->typeProcessMap($value);

            if(!empty($value['EVENT_ID']) && in_array($value['NAME'], $moduleConfig['clear_ocurrence_ref'])){
               $clearOcurrenceList[] = $value['EVENT_ID'];
               continue;
            };
         
            $hash = md5(implode(",", $value));
            if(in_array($hash, $exampleHash)){
               $errors[$key+1][] = i::__("Linha invalida. Os dados da linha são os dados do exemplo, apague a mesma para continuar");
               break;
            }
            
            $value['STARTS_AT'] = $this->formatDate($value['STARTS_AT'], "H:i");
            $value['ENDS_AT'] = $this->formatDate($value['ENDS_AT'], "H:i");
            $value['STARTS_ON'] = $this->formatDate($value['STARTS_ON'], "Y-m-d");
            $value['ENDS_ON'] = $this->formatDate($value['ENDS_ON'], "Y-m-d");
         

            if(!empty($value['EVENT_ID'])){
               if(!$conn->fetchAll("SELECT * FROM event WHERE status >= 1 AND id = '{$value['EVENT_ID']}'")) {
                  $errors[$key+1][] = i::__("O evento não está cadastrado");
               }
            }

            if(!empty($value['SEAL_ID'])){
               if(!$conn->fetchAll("SELECT * FROM seal WHERE status >= 1 AND id = '{$value['SEAL_ID']}'")) {
                  $errors[$key+1][] = i::__("O selo não está cadastrado");
               }
            }

            if($type_process_map->create_event){
               if(empty($value['NAME']) || $value['NAME'] == ''){
                  $errors[$key+1][] = i::__("A coluna nome está vazia");
               }

               if(empty($value['SHORT_DESCRIPTION']) || $value['SHORT_DESCRIPTION'] == ''){
                  $errors[$key+1][] = i::__("A coluna descrição curta está vazia");
               }

               if(empty($value['CLASSIFICATION']) || $value['CLASSIFICATION'] == ''){
                  $errors[$key+1][] = i::__("A coluna classificação estária está vazia");
               }

               if (!in_array($this->lowerStr($value['CLASSIFICATION']),$moduleConfig['rating_list_allowed'])) {
                  $rating_str = implode(', ',$moduleConfig['rating_list_allowed']);
                  $errors[$key+1][] = i::__("A coluna classificação etária é inválida. As opções aceitas são {$rating_str}");
               }

               //Validação das linguagens
               $languages = explode(';', $value['LANGUAGE']);
               if (!$languages) {
                  $errors[$key+1][] = i::__("A coluna linguagem está vazia");
               }

               //Tratamento da lista
               $languages_list = $app->getRegisteredTaxonomyBySlug('linguagem')->restrictedTerms;

               foreach ($languages as $language) {
                  $_language = $this->lowerStr($language);

                  if (!in_array(trim($_language), array_keys($languages_list))) {
                     $errors[$key+1][] = i::__("A linguagem {$_language} não existe");
                  }
               }

               // Validação das tags
               $tags = [];
               if($value['TAGS']){
                  $tags = explode(';', $value['TAGS']);
               }
   
               //Validação do projeto
               if($value['PROJECT']){
                  $collum = $this->checkCollum($value['PROJECT']);
                  if(!$projects = $conn->fetchAll("SELECT * FROM project WHERE status >= 1 AND {$collum} = '{$value['PROJECT']}'")) {
                     $errors[$key+1][] = i::__("O projeto não está cadastrado");
                  }
         
                  if ($collum == 'name') {
                     if (count($projects) > 1){
                        $errors[$key+1][] = i::__("Existe mais de um projeto com o nome {$value['PROJECT']}. Para proseguir, informe o ID do projeto que quer associar ao evento");
                     }
                  }
               }

               //Validação do agente responsavel 
               if(!is_numeric($value['OWNER'])){
                  $errors[$key+1][] = i::__("A coluna proprietário espera o número ID do agente. ");
               }else{
                  if(empty($value['OWNER']) || ($value['OWNER'] == "")){
                     $errors[$key+1][] = i::__("A coluna agente é obrigatória. Informo o ID do agente responsável");
                  } else if(!$conn->fetchAll("SELECT * FROM agent WHERE status >= 1 AND id = {$value['OWNER']}")) {
                     $errors[$key+1][] = i::__("O a gente não esta cadastrado");
                  }
               }
            }
       
            if( $type_process_map->create_event || $type_process_map->edit_ocurrence){
               $collum_spa = 'id';
               if (!is_numeric($value['SPACE'])) {
                  $collum_spa = 'name';
               }

               $collum = $this->checkCollum($value['SPACE']);
               if(!$spaces = $conn->fetchAll("SELECT * FROM space WHERE status >= 1 AND {$collum} = '{$value['SPACE']}'")) {
                  $errors[$key+1][] = i::__("O espaço não está cadastrado");
               }

               if ($collum_spa == 'name') {
                  if (count($spaces) > 1) {
                     $errors[$key+1][] = i::__("Existe mais de um espaço com o nome {$value['SPACE']}. Para proseguir informe o ID do espaço que quer associar ao evento");
                  }
               }

               //Verificação da frequencia
               if(empty($value['FREQUENCY']) || $value['FREQUENCY'] == ''){
                  $errors[$key+1][] = i::__("A coluna frequência está vazia");
               }

               if (!in_array($value['FREQUENCY'], array_keys($moduleConfig['frequence_list_allowed']))) {
                  $frequence_str = implode(', ', array_keys($moduleConfig['frequence_list_allowed']));
                  $errors[$key+1][] = i::__("A frequência é inválida. As opções aceitas são {$frequence_str}");
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
               if(empty($value['STARTS_AT']) || $value['STARTS_AT'] == ''){
                  $errors[$key+1][] = i::__("A coluna Hora inícial está vazia");
               }   
               
               $starts_at = $this->formatDate($value['STARTS_AT'], "H:i");
               if($starts_at != $value['STARTS_AT']){
                  $errors[$key+1][] = i::__("A coluna Hora inícial é inválida. O formato esperado é HH:MM Ex.: 12:00");
               }
               
               // Valida a hora final
               if(empty($value['ENDS_AT']) || $value['ENDS_AT'] == ''){
                  $errors[$key+1][] = i::__("A coluna Hora final está vazia");
               }
               
               $ends_at = $this->formatDate($value['ENDS_AT'], "H:i");
               if($ends_at != $value['ENDS_AT']){
                  $errors[$key+1][] = i::__("A coluna Hora final é inválida. O formato esperado é HH:MM Ex.: 12:00");
               }
               
               
               if((new DateTime($value['STARTS_AT'])) > (new DateTime($value['ENDS_AT']))){
                  $errors[$key+1][] = i::__("A data inicial é maior que a data final.");
               }

               // Valida a data inicial
               if (empty($value['STARTS_ON']) || $value['STARTS_ON'] == "") {
                  $errors[$key+1][] = i::__("A Coluna Data inícial Está vazia");
               }

               $starts_on = $this->formatDate($value['STARTS_ON'], "Y-m-d");
               if ($starts_on != $value['STARTS_ON']) {
                  $errors[$key+1][] = i::__("A coluna data inícial é inválida. O formato esperado é DD/MM/YYYY Ex.: 01/01/2022");
               }
               
               // Valida a data final
               if(in_array($value['FREQUENCY'], $moduleConfig['use_endson'])){
                  if (empty($value['ENDS_ON']) || $value['ENDS_ON'] == "") {
                     $errors[$key+1][] = i::__("A Coluna Data final Está vazia");
                  }
                  
                  $ends_on = $this->formatDate($value['ENDS_ON'], "Y-m-d");
                  if ($ends_on != $value['ENDS_ON']) {
                     $errors[$key+1][] = i::__("A coluna data final é inválida. O formato esperado é DD/MM/YYYY Ex.: 01/01/2022");
                  }
               }

               if($value['EVENT_ATTENDANCE'] && !is_numeric($value['EVENT_ATTENDANCE'])){
                  $errors[$key+1][] = i::__("A coluna total de público é inválida. Essa coluna so aceita números");
               }
            }
         }
      }

      if($errors){
         $this->render("import-erros", ["errors" => $errors, 'filename' => basename($file_dir)]);
         exit;
      }

      $countNewEvent = 0;
      $eventsIdList = [];
      $error_process = false;
      $deletedOcurrences = [];
      $process_file = json_decode($app->user->profile->event_importer_files_processed, true);
      if (!in_array($file->id, $process_file)) {
         $process_file[] = $file->id;
         $app->user->profile->event_importer_files_processed = json_encode($process_file);
         $app->user->profile->save(true);

         foreach ($data as $key => $value) {
            $type_process_map = $this->typeProcessMap($value);

            if (!empty($value['EVENT_ID']) && in_array($value['NAME'], $moduleConfig['clear_ocurrence_ref'])) {
               continue;
            };

            $app->em->beginTransaction();
            if ($type_process_map->create_event || $type_process_map->edit_event) {

               if ($type_process_map->edit_event && in_array($value['EVENT_ID'], $clearOcurrenceList) && !in_array($value['EVENT_ID'], $deletedOcurrences)) {
                  if ($ocurrences = $app->repo("EventOccurrence")->findBy(["eventId" => $value['EVENT_ID']])) {
                     foreach ($ocurrences as $ocurrence) {
                        $ocurrence->delete(true);
                     }
                     $deletedOcurrences[] = $value['EVENT_ID'];
                  }
               }

               if ($event = $this->insertEvent($value)) {

                  $ocurrence = true;
                  if ($type_process_map->create_ocurrence || $type_process_map->edit_ocurrence) {
                     $ocurrence = $this->createOcurrency($event, $value, $key);
                  }

                  $file = $this->downloadFile($event, $value);
                  $metalist = $this->createMetalists($event, $value);


                  if ($ocurrence && $file && $metalist) {
                     $countNewEvent++;
                     $eventsIdList[$event->id] = $event->id;
                     $this->ApplySeal($event, $value);
                     $app->em->commit();
                  } else {
                     $error_process = true;
                     $errors[$key + 1][] = i::__("Evento {$event->name} Não foi inserido");
                  }
               }
            } else if ($type_process_map->delete_event) {
               $event = $app->repo('Event')->find($value['EVENT_ID']);
               $event->delete(true);
               $countNewEvent++;
               $eventsIdList[] = $event->id;
               $app->em->commit();
            }
         }

         if ($countNewEvent >= 1) {
            $_agent = $app->user->profile;
            $files = $_agent->event_importer_processed_file ?? new stdClass();
            $files->{basename($file_dir)} = [
               'date' => date('d/m/Y \à\s H:i'),
               'countProsess' => $countNewEvent,
               'eventsIdList' => $eventsIdList,
               'typeFile' => ($type_process_map->create_event ? i::__('Criação') : ($type_process_map->edit_event ? i::__('Edição') : ($type_process_map->delete_event ? i::__('Deleção') : i::__('Não definido'))))
            ];
            $_agent->event_importer_processed_file = $files;
            $_agent->save(true);

            if ($error_process) {
               $this->render("import-erros", ["errors" => $errors, 'filename' => basename($file_dir)]);
               exit;
            }
         } else {
            $this->render("import-erros", ["errors" => $errors, 'filename' => basename($file_dir)]);
            exit;
         }
      }
      

      $url = $app->createUrl("painel", "eventos");
      $app->redirect($url."#tab=event-importer");
      
   }

   public function checkCollum($value)
   {
      $collum = 'id';
      if (!is_numeric($value)) {
         $collum = 'name';
      }
      return $collum;
   }


   public function insertEvent($value)
   {
      try {
         $app = App::i();

         $type_process_map = $this->typeProcessMap($value);

         $moduleConfig = $app->modules['EventImporter']->config;
         
         $collum_proj = $this->checkCollum($value['PROJECT']);
         $project =  null;
         if($value['PROJECT']){
            $project = $app->repo("Project")->findOneBy([$collum_proj => $value['PROJECT']]);
         }

         $agent =  null;
         if($value['OWNER']){
            $agent = $app->repo('Agent')->find($value['OWNER']);
         }
         $languages = explode(';', $value['LANGUAGE']);
         
         $tags = [];
         if($value['TAGS']){
            $tags = explode(';', $value['TAGS']);
         }

         $more_information =  null;
         if($value['MORE_INFORMATION']){
            $more_information = preg_replace('/[^0-9]/i', '', $value['MORE_INFORMATION']);
         }

         $event = new Event();
         if($type_process_map->edit_event){
            $event = $app->repo('Event')->find($value['EVENT_ID']);

            foreach($moduleConfig['fromToEntity']['event'] AS $field => $v){

               if($field == "LANGUAGE"){
                  $languages = array_merge($event->terms[$v], $languages);
               }else if($field == "TAGS"){
                  $tags = array_merge($event->terms[$v], $tags);
               }else if($field == "OWNER"){
                  $agent = $agent ?: $event->owner;
               }else{
                  $_field = $moduleConfig['fromToEntity']['event'][$field];
                  $value[$field] = $value[$field] ?: $event->$_field;

               }
            }
         }
                 
         $_languages = [];
         if($languages){
            $languages_list = $app->getRegisteredTaxonomyBySlug('linguagem')->restrictedTerms;
            foreach(array_filter($languages) as $language){
               $_lang = $this->lowerStr($language);
               $_languages[] = $languages_list[$_lang];
            }
         }

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
         $event->terms['linguagem'] = $_languages;
         $event->projectId = $project ? $project->id : null;
         $event->event_attendance = $value['EVENT_ATTENDANCE'];
         $event->traducaoLibras = $value['LIBRAS_TRANSLATION'];
         $event->telefonePublico = $more_information;
         $event->descricaoSonora = $value['AUDIO_DESCRIPTION'];
         $event->terms['tag'] = $tags;
         
         if($agent){
            $event->owner = $agent;
         }
         
         $event->save(true);
         return $event;
      } catch (\Throwable $th) {
         return false;
      }         
   }


   public function createOcurrency($event, $value, $key)
   {
      try {
         $app = App::i();

         $moduleConfig = $app->modules['EventImporter']->config;
   
         $freq = $this->lowerStr($value['FREQUENCY']);
         $ocurrence = new EventOccurrence();    
   
         $duration = function() use ($value){
            $start = $this->formatDate($value['STARTS_AT']);
            $stop = $this->formatDate($value['ENDS_AT']);
            $diferenca = strtotime($stop) - strtotime($start);
   
            return ($diferenca / 60);
         };
   
         $collum = $this->checkCollum($value['SPACE']);
         $space = $app->repo("Space")->findOneBy([$collum => $value['SPACE']]);
   
         $rule = [
            "spaceId" => $space->id,
            "startsOn" => $this->formatDate($value['STARTS_ON'], "Y-m-d"),
            "duration" => $duration(),
            "frequency" => $moduleConfig['frequence_list_allowed'][$freq],
            "startsAt" => $this->formatDate($value['STARTS_AT'], "H:i"),
            "until" => (!empty($value['ENDS_ON']) && $value['ENDS_ON'] !="")? $this->formatDate($value['ENDS_ON'], "Y-m-d") :null,
            "price" => $value['PRICE'],
            "description" => "",
         ];
        
         switch ($this->lowerStr($value['FREQUENCY'])) {
            case i::__('diariamente'):
            case i::__('todos os dias'):
            case i::__('diario'):
            case i::__('daily'):
               $exec = function () use (&$ocurrence, $value, $app, &$rule) {
   
                  $ocurrence->endsAt = $this->formatDate($value['ENDS_AT'], false);
                  $rule['description'].= i::__('Diariamente');
   
                  $months[$value['STARTS_ON']] = $value['STARTS_ON'];
                  $months[$value['ENDS_ON']] = $value['ENDS_ON'];
                  
                  $_months = array_keys($months);
               
                  $dateIn = $this->formatDate($_months[0], false);
                  $dateFn = $this->formatDate($_months[1], false);
                  
                  $years[$dateIn->format("Y")] = $dateIn->format("Y");
                  $years[$dateFn->format("Y")] = $dateFn->format("Y");
                  $_years = array_keys($years);
   
                  $yearIn = null;
                  $yearFn = null;
                  if(count($_years) == 1){
                     $yearFn = " de ".$this->formatDate($_years[0], "Y");
                  }else{
                     if(isset($_years[0]) && isset($_years[0])){
                        $yearIn = " de ".$this->formatDate($_years[0], "Y");
                        $yearFn = " de ".$this->formatDate($_years[1], "Y");
                     }else{
                        $yearFn = " de ".$this->formatDate($_years[0], "Y");
                     }
                  }
                 
                  $start = $this->formatDate($value['STARTS_AT'], false);
                  if(count($_months) == 1){
                     $dateFn = $this->formatDate($_months[1], false);
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
   
                  $ocurrence->endsOn = $this->formatDate($value['ENDS_ON'], false);
   
                  $moduleConfig = $app->modules['EventImporter']->config;
   
                  $week_days = array_keys($moduleConfig['week_days']);
                  $days_list_positive = $moduleConfig['days_list_positive'];
   
                  $days = [];
                  foreach ($week_days as $key => $day) {
                     if (in_array($value[$day], $days_list_positive)) {
                        $days[$key] = "on";
                     }
                  }
   
                  $rule['endsOn'] = $this->formatDate($value['ENDS_ON'], "Y-m-d");
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
      
                        $count;
                     }
   
                     $rule['description'].= i::__(' ');
                  }
                 
                  $months[$value['STARTS_ON']] = $value['STARTS_ON'];
                  $months[$value['ENDS_ON']] = $value['ENDS_ON'];
   
                  $_months = array_keys($months);
   
                  $dateIn = $this->formatDate($_months[0], false);
                  $dateFn = $this->formatDate($_months[1], false);
                  
                  $years[$dateIn->format("Y")] = $dateIn->format("Y");
                  $years[$dateFn->format("Y")] = $dateFn->format("Y");
                  $_years = array_keys($years);
                 
                  $yearIn = null;
                  $yearFn = null;
                  if(count($_years) == 1){
                     $yearFn = i::__(" de ".$this->formatDate($_years[0], "Y"));
                  }else{
                     if(isset($_years[0]) && isset($_years[0])){
                        $yearIn = i::__(" de ".$this->formatDate($_years[0], "Y"));
                        $yearFn = i::__(" de ".$this->formatDate($_years[1], "Y"));
                     }else{
                        $yearFn = i::__(" de ".$this->formatDate($_years[0], "Y"));
                     }
                  }
               
                  $start = $this->formatDate($value['STARTS_AT'], false);
                  if(count($_months) == 1){
                     $rule['description'].= i::__("de {$dateIn->format("d")} a {$dateFn->format("d")} de  {$dateIn->format("F")} {$yearFn}  às {$start->format("H:i")}");
                  }else{
                     $dateFn = $this->formatDate($_months[1], false);
                     $rule['description'].= i::__("de {$dateIn->format("d")} de {$dateIn->format("F")} {$yearIn} a {$dateFn->format("d")} de {$dateFn->format("F")} {$yearFn} às {$start->format("H:i")}");
                  }
                  
               };
               break;
            case i::__('uma vez'):
            case i::__('once'):
               $exec = function () use ($ocurrence, $value, $app, &$rule) {
   
                  $dateIn = $this->formatDate($value['STARTS_ON'], false);
                  $start = $this->formatDate($value['STARTS_AT'], false);
   
                  $rule['description'].= i::__("Dia {$dateIn->format("d")} de {$dateIn->format("F")} de {$dateIn->format("Y")} às {$start->format("H:i")}");
               };
               break;
         }
   
         $exec();
         
         $from = array_keys($moduleConfig['dic_months']);
         $to = array_values($moduleConfig['dic_months']);
         $rule['description'] = str_replace($from, $to, $rule['description']);
         
         $ocurrence->startsAt = $this->formatDate($value['STARTS_AT'], false);
         $ocurrence->endsAt = $this->formatDate($value['ENDS_AT'], false);
         $ocurrence->startOn = $this->formatDate($value['STARTS_ON'], false);
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
         return true;
      } catch (\Throwable $th) {
         return false;
      }
   }

   protected function downloadFile(Entity $owner, $value)
   {
      $app = App::i();

      $moduleConfig = $app->modules['EventImporter']->config;
      
      $files_grp_import = $moduleConfig['files_grp_import'];
      
      $no_error = true;
      foreach ($files_grp_import as $key => $grp_import) {

      
         if(empty($value[$key])){
            continue;
         }
     
         if(!empty($value[$key]) || $value[$key] != ""){
               if($key == "GALLERY" || $key == "DOWNLOADS"){
                 
               $gallery_list = $this->matches($value[$key]);
   
               foreach($gallery_list as $item){
                  if(!$this->saveFile($item, $owner, $grp_import) && !$no_error){
                     $no_error = false;
                  }
               }
            }else{
               if(!$this->saveFile($value[$key], $owner, $grp_import) && !$no_error){
                  $no_error = false;
               }
            }
         }
      }

      return $no_error;
   }

   public function saveFile($value, $owner, $grp_import)
   {
      
      try {
        
         $exp = explode(":", $value);

         $_file = $exp[0].":".$exp[1];
         $description = isset($exp[2]) ? $exp[2] : null;

         $basename = basename($_file);
         $file_data = str_replace($basename, urlencode($basename), $_file);

         $curl = new Curl;
         $curl->get($file_data);
         $curl->close();
         $response = $curl->response;

         $tmp = tempnam("/tmp", "");
         $handle = fopen($tmp, "wb");

         if(mb_strpos($response, 'html')){
            fclose($handle);
            unlink($tmp);
            return false;
         }

         if(!$this->urlFileExists($_file)){
            fclose($handle);
            unlink($tmp);
            return false;
         }

         fwrite($handle,$response);
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
         return true;
      } catch (\Throwable $th) {
         return false;
      }
   }


   public function createMetalists(Entity $owner, $value)
   {
      try {
         $app = App::i();

         $moduleConfig = $app->modules['EventImporter']->config;
         
         $metalists_import = $moduleConfig['metalists_import'];
         foreach($metalists_import as $key => $metalist){

            if(empty($value[$metalist])){
               continue;
            }

            $lists = $this->matches($value[$metalist]);
            foreach($lists as $item){
               $exp = explode(":", $item);

               $url = $exp[0].":".$exp[1];
               $title = isset($exp[2]) ? $exp[2] : null;

               $metaList = new MetaList();
               $metaList->owner = $owner;
               $group = mb_strtolower($metalist);
               $metaList->group = $group;
               $metaList->title = $title ?? "" ;
               $metaList->value = $url ?? "";
               $metaList->save(true);
            }
         }
         return true;
      } catch (\Throwable $th) {
         return false;
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

   public function formatDate($date, $formatOut = "Y-m-d H:i")
   {
      if(!$date){
         return null;
      }

      $formats = [
         'd/m/Y',
         'd/m/Y H',
         'd/m/Y H:i',
         'd/m/Y H:i:s',
         'Y-m-d',
         'Y-m-d H',
         'Y-m-d H:i',
         'Y-m-d H:i:s',
         'H:i:s',
         'H:i',
         'Y'
      ];

      foreach ($formats as $format) {
         $objDate = DateTime::createFromFormat($format, $date);
         if ($objDate !== false) {
            break;
         }
      }
      
      if ($objDate === false) {
         return (new DateTime('1989-01-01'))->format($formatOut);
      }

      if ($formatOut) {
         return $objDate->format($formatOut);
      } else {
         return $objDate;
      }
   }

   public function lowerStr($value)
   {
      return $value ? trim(mb_strtolower($value)) : "";
   }

   public function dispatch($file_name, $path)
   {
      header('Content-Disposition: attachment; filename=' . $file_name);
      header('Pragma: no-cache');
      readfile($path);
      unlink($path);
   }
}
