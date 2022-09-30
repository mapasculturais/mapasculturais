<?php

namespace EventImporter;

use Exception;
use ZipArchive;
use League\Csv\Reader;
use FilesystemIterator;
use MapasCulturais\App;
use League\Csv\Statement;
use MapasCulturais\Entities\Event;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Controller extends \MapasCulturais\Controller
{

   function GET_uploadFile()
   {
      $app = App::i();

      $request = $this->data;
      $file = $app->repo('File')->find($request['file']);
      $file_dir = $file->path;

      if (file_exists($file_dir)) {
         $ext = pathinfo($file_dir, PATHINFO_EXTENSION);
         $csv = $file_dir;

         if ($ext === 'zip') {
            $csv = $this->unzipFile($file_dir);
         }

         $data = $this->processCSV($csv);
      }
   }

   //Manipula arquivos compactados
   public function unzipFile(string $file_dir)
   {
      $request = $this->data;

      $csv_file_dir = [];
      if (file_exists($file_dir)) {
         $dir_base = dirname($file_dir);
         $extract_dir = $dir_base . '/' . md5($file_dir . '_file' . $request['file']);

         $zip = new ZipArchive();
         if ($zip->open($file_dir)) {
            $zip->extractTo($extract_dir);
            $zip->close();

            if (file_exists($extract_dir)) {

               $allowed_files = ['text/csv', 'image/jpeg', 'image/png', 'image/jpg', 'text/plain'];

               $recusive_dir = new RecursiveDirectoryIterator($extract_dir, FilesystemIterator::SKIP_DOTS);
               $files = new RecursiveIteratorIterator($recusive_dir);
               $finfo = finfo_open(FILEINFO_MIME_TYPE);

               foreach ($files as $file) {
                  $mime = finfo_file($finfo, $file);
                  if (!in_array($mime, $allowed_files)) {
                     die("Arquivo {$file} é inválido");
                  }
               }

               $csv_file_dir = glob($extract_dir . '/*.csv');
            }
         }
      }
      return $csv_file_dir[0];
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
