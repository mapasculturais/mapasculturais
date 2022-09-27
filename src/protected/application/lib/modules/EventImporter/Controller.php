<?php

namespace EventImporter;

use League\Csv\Reader;
use MapasCulturais\App;
use League\Csv\Statement;
class Controller extends \MapasCulturais\Controller
{

   function GET_uploadFile()
   {
      $app = App::i();

      $request = $this->data;

      $file = $app->repo('File')->find($request['file']);
      $file_dir = $file->path;
      
      if(file_exists($file_dir)){
         $ext = pathinfo($file_dir, PATHINFO_EXTENSION);
         $csv = $file_dir;
         if($ext === 'zip'){
            $csv = $this->unzipFile($file_dir);
         }

         $this->processCSV($csv);

      }
      
   }

   //Manipula arquivos compactados
   public function unzipFile(string $file_dir){
      $csv = null;
      return $csv;
   }

   //Processa arquivos CSV
   
   public function processCSV(string $file_dir){

      $stream = fopen($file_dir,'r');

      $csv = Reader::createFromStream($stream);
      $csv->setDelimiter(";");
      $csv->setHeaderOffset(0);
      
      $stm = (new Statement());
      $data = $stm->process($csv);

      $result = [];
      foreach($data as $value){
         $result[]=$value;
      }
      
   }
}
