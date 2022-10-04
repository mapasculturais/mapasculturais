<?php

namespace EventImporter;

use Exception;
use League\Csv\Reader;
use MapasCulturais\App;
use League\Csv\Statement;
use MapasCulturais\Entities\Event;

class Controller extends \MapasCulturais\Controller
{

   function GET_uploadFile()
   {
      $app = App::i();

      $request = $this->data;
      $file = $app->repo('File')->find($request['file']);
      $file_dir = $file->path;

      if (file_exists($file_dir)) {
         $data = $this->processCSV($file_dir);
      }else{
         throw new Exception("Arquivo CSV não existe. Erro ao processar");
      }
   }

   //Processa arquivos CSV
   public function processCSV(string $file_dir)
   {
      $app = App::i();

      $stream = fopen($file_dir, 'r');

      $csv = Reader::createFromStream($stream);
      $csv->setDelimiter(";");
      $csv->setHeaderOffset(0);

      $stm = (new Statement());
      $data = $stm->process($csv);

        // Verificar se no csv existe as colunas minimas para cadastrar um evento "Colunas Obrigatorias"

         // Mapear as colunas 
      foreach ($data as $value) {

         if (!$agent = $app->repo('Agent')->find($value['OWNER'])) {
            throw new Exception("O a gente não esta cadastrado");
         }
         $collum = 'id';
         if (!is_numeric($value['PROJECT'])) {
            $collum = 'name';
         }

         if (!$project = $app->repo('Project')->findOneBy([$collum => $value['PROJECT']])) {
            throw new Exception("O Projeto Não esta cadastrado");
         }

         $languages = explode(',', $value['LANGUAGE']);
         if (!$languages) {
            throw new Exception("Langues esta vazia");
         }

         $event = new Event();
         $event->name = $value['NAME'];
         $event->shortDescription = $value['SHORT_DESCRIPTION'];
         $event->classificacaoEtaria = $value['CLASSIFICATION'];
         $event->owner = $agent;
         $event->terms['linguagem'] = $languages;
         $event->projectId = $project->id;
         $event->save(true);
      }
   }
}
