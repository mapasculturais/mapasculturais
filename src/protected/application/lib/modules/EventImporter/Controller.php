<?php

namespace EventImporter;

use Exception;
use League\Csv\Reader;
use MapasCulturais\App;
use League\Csv\Statement;
use MapasCulturais\Controllers\Space;
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

      $moduleConfig = $app->modules['EventImporter']->config;

      $stream = fopen($file_dir, 'r');

      $csv = Reader::createFromStream($stream);
      $csv->setDelimiter(";");
      $csv->setHeaderOffset(0);

      $stm = (new Statement());
      $data = $stm->process($csv);

      // Verificar se no csv existe as colunas minimas para cadastrar um evento "Colunas Obrigatorias"

      // Mapear as colunas 
      foreach ($data as $key => $value) {

         if(empty($value['NAME']) || $value['NAME'] == ''){
            throw new Exception("A coluna nome está vazia na linha {$key}");
         }

         if(empty($value['SHORT_DESCRIPTION']) || $value['SHORT_DESCRIPTION'] == ''){
            throw new Exception("A coluna descrição curta está vazia na linha {$key}");
         }

         if(empty($value['CLASSIFICATION']) || $value['CLASSIFICATION'] == ''){
            throw new Exception("A coluna classificação estária está vazia na linha {$key}");
         }
         if (!in_array($value['CLASSIFICATION'],$moduleConfig['rating_list'])) {
            $rating_str = implode(', ',$moduleConfig['rating_list']);
            throw new Exception("A classificação etária é inválida {$key}. As opções aceitas são --{$rating_str}--");
         }

         //Validação das linguagens
         $languages = explode(',', $value['LANGUAGE']);
         if (!$languages) {
            throw new Exception("Linguagem está vazia na linha {$key}");
         }

         //Tratamento da lista
         $languages_list = $app->getRegisteredTaxonomyBySlug('linguagem')->restrictedTerms;

         foreach ($languages as $language) {
            $_language = mb_strtolower($language);

            if (!in_array($_language, array_keys($languages_list))) {
               throw new Exception("linguagem{$_language} não existe");
            }
         }

         //Validação do projeto
         $collum_proj = 'id';
         if (!is_numeric($value['PROJECT'])) {
            $collum_proj = 'name';
         }

         if (!$projects = $app->repo('Project')->findBy([$collum_proj => $value['PROJECT']])) {
            throw new Exception("O Projeto Não está cadastrado na linha {$key}");
         }

         if ($collum_proj == 'name') {
            if (count($projects) > 1){
               throw new Exception("Existem mais de um projeto com o nome {$value['PROJECT']}, Para proseguir informe o ID do projeto que quer associar ao evento");
            }
         }

         //Validação do agente responsavel 
         if (!$agent = $app->repo('Agent')->find($value['OWNER'])) {
            throw new Exception("O a gente não esta cadastrado");
         }

         //Validação do espaço
         $collum_spa = 'id';
         if (!is_numeric($value['SPACE'])) {
            $collum_spa = 'name';
         }

         if (!$spaces = $app->repo('Space')->findBy([$collum_spa => $value['SPACE']])) {
            throw new Exception("O espaço não esta cadastrado");
         }

         if ($collum_spa == 'name') {
            if (count($spaces) > 1) {
               throw new Exception("Existem mais de um espaço com o nome {$value['SPACE']}, Para proseguir informe o ID do espaço que quer associar ao evento");
            }
         }

         //Verificação da frequencia
         if(empty($value['FREQUENCY']) || $value['FREQUENCY'] == ''){
            throw new Exception("A coluna Frequência está vazia na linha {$key}");
         }
         if (!in_array($value['FREQUENCY'],$moduleConfig['frequence_list'])) {
            $frequence_str = implode(', ',$moduleConfig['frequence_list']);
            throw new Exception("A Frequência é inválida na linha {$key}. As opções aceitas são --{$frequence_str}-- ");
         }
         
         //criação do enveto
         $event = new Event();
         $event->name = $value['NAME'];
         $event->shortDescription = $value['SHORT_DESCRIPTION'];
         $event->classificacaoEtaria = $value['CLASSIFICATION'];
         $event->owner = $agent;
         $event->terms['linguagem'] = $languages;
         $event->projectId = $projects[0]->id;
         $event->save(true);
      }
}
}
