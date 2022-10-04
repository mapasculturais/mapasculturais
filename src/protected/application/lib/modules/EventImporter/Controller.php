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

      $stream = fopen($file_dir, 'r');

      $csv = Reader::createFromStream($stream);
      $csv->setDelimiter(";");
      $csv->setHeaderOffset(0);

      $stm = (new Statement());
      $data = $stm->process($csv);
      
      // Verificar se no csv existe as colunas minimas para cadastrar um evento "Colunas Obrigatorias"
      
      // Mapear as colunas 
      foreach ($data as $key => $value) {
         
         //Validação das linguagens
         $languages = explode(',', $value['LANGUAGE']);
         if (!$languages) {
            throw new Exception("Linguagem está vazia na linha {$key}");
         } 
         
         //Tratamento da lista >>>
         $languages_list = $app->getRegisteredTaxonomyBySlug('linguagem')->restrictedTerms;

         //Fazer um foreach para percorrer a varialvel $languages 
         foreach($languages as $language){
         
         // 1 - Criar uma variavel $_language e Passar a $language para mb_strtolower($language)
         // 2 - Verificar se o valor $_language esta presente nas chaves do $languages_list. Pesquisa no google array_key para pegar somente as chaves do $languages_list. Pesquisar também como verificar se um valor existe dentro de um array 
         // 3 - Se o valor do $_language não existir na lista languages_list deve mostrar um erro na tela falando que a linguagem não existe
         }

         //Validação do projeto
         $collum_proj = 'id';
         if (!is_numeric($value['PROJECT'])) {
            $collum_proj = 'name';
         }
         
         if (!$projects = $app->repo('Project')->findBy([$collum_proj => $value['PROJECT']])) {
            throw new Exception("O Projeto Não está cadastrado na linha {$key}");
         } 

         if($collum_proj =='name'){
            if(count($projects) >1){
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
