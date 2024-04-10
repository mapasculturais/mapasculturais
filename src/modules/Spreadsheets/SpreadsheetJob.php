<?php

namespace Spreadsheets;

use MapasCulturais\App;
use MapasCulturais\Definitions;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Job;
use MapasCulturais\i;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Ods;

/**
 * @property-read string $fileGroup
 * @package Spreadsheets
 */
abstract class SpreadsheetJob extends JobType
{
    protected $page = 1;

    function __construct(string $slug, protected int $limit = 2)
    {
        parent::__construct($slug);

        $app = App::i();

        foreach ($this->targetEntities as $target_entity) {
            $app->registerFileGroup(
                $target_entity::getControllerId(),
                new Definitions\FileGroup(
                    $this->fileGroup,
                    ['text/csv', 'application/excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'],
                    'O arquivo não é válido',
                    private: true
                )
            );
        }
    }

    protected function _execute(Job $job)
    {
        //$app = App::i();
        $entity_class_name = $job->entityClassName;
        $file_class = $entity_class_name::getFileClassName();

        $extension = $job->extension ?: 'xlsx';
        $filename = $this->getFilename($job);

        $path = sys_get_temp_dir() . '/' . $filename;
        $header = $this->getHeader($job);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($header, null, "A1");

        $row = 2;
        while($batch = $this->getBatch($job)) {
            foreach ($batch as $data) {
                $new_data = [];
                foreach($header as $prop => $label) {
                    $new_data[] = $data[$prop]; 
                } 

                $sheet->fromArray($new_data, null, "A$row");
                $row++;
            }
        }

        if($extension === 'xlsx') {
            $writer = new Xlsx($spreadsheet);
        } else if($extension === 'csv') {
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(';');
        } else {
            $writer = new Ods($spreadsheet);
        }

        $writer->save($path);
        
        $mimeTypes = [
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
        ];

        /** @var \MapasCulturais\Entities\File */
        $file = new $file_class([
            'error' => UPLOAD_ERR_OK,
            'name' => $filename,
            'type' => $mimeTypes[$extension],
            'tmp_name' => $path,
            'size' => filesize($path),
        ]);

        $file->group = 'downloads';
        $file->owner = $job->owner->profile;
        $file->save(true);

        // Disparo de e-mail de teste
       $this->mailNotification($job->authenticatedUser);
    }

    function mailNotification($user)
    {
        $app = App::i();

        $template = 'start_teste';
        $teste = [
            'userName' => $user->profile->name,
        ];

        $message = $app->renderMailerTemplate($template, $teste);

        $app->createAndSendMailMessage([
            'from' => $app->config['mailer.from'],
            'to' => $user->email,
            'subject' => sprintf(i::__($message['title'], Agent::class)),
            'body' => $message['body']
        ]);
    }

    function getHeader(Job $job)
    {
        $app = App::i();

        $app->applyHookBoundTo($this, "SpreadsheetJob($this->slug).getHeader:before", [$job]);

        $result = $this->_getHeader($job);

        $app->applyHookBoundTo($this, "SpreadsheetJob($this->slug).getHeader:after", [$job, &$result]);

        return $result;
    }

    function getBatch(Job $job)
    {
        $app = App::i();

        $app->applyHookBoundTo($this, "SpreadsheetJob($this->slug).getBatch:before", [$job]);
        
        $result = $this->_getBatch($job);
        $this->page++;

        $app->applyHookBoundTo($this, "SpreadsheetJob($this->slug).getBatch:after", [$job, &$result]);

        return $result;
    }

    function getFileGroup()
    {
        $app = App::i();

        $app->applyHookBoundTo($this, "SpreadsheetJob($this->slug).getFileGroup:before", []);

        $result = $this->_getFileGroup();

        $app->applyHookBoundTo($this, "SpreadsheetJob($this->slug).getFileGroup:after", [&$result]);

        return $result;
    }

    function getTargetEntities()
    {
        $app = App::i();

        $app->applyHookBoundTo($this, "SpreadsheetJob($this->slug).getTargetEntities:before");

        $result = $this->_getTargetEntities();

        $app->applyHookBoundTo($this, "SpreadsheetJob($this->slug).getTargetEntities:after", [&$result]);

        return $result;
    }

    function getFilename(Job $job)
    {
        $app = App::i();

        $app->applyHookBoundTo($this, "SpreadsheetJob($this->slug).getFilename:before", [$job]);

        $result = $this->_getFilename($job);

        $app->applyHookBoundTo($this, "SpreadsheetJob($this->slug).getFilename:after", [$job, &$result]);

        return $result;
    }

    /**
     * 
     * @return string 
     */
    abstract protected function _getFileGroup(): string;

    /**
     * 
     * @return string[]
     */
    abstract protected function _getTargetEntities(): array;

    /**
     * 
     * @return array
     */
    abstract protected function _getHeader(Job $job): array;

    /**
     * 
     * @return array[]
     */
    abstract protected function _getBatch(Job $job): array;

    /**
     * 
     * @return string
     */
    abstract protected function _getFilename(Job $job): string;
}
