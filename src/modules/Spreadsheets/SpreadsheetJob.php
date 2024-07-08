<?php

namespace Spreadsheets;

use MapasCulturais\App;
use MapasCulturais\Definitions;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\Job;
use MapasCulturais\i;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * @property-read string $fileGroup
 * @package Spreadsheets
 */
abstract class SpreadsheetJob extends JobType
{
    protected $page = 1;

    function __construct(string $slug, protected int $limit = 50)
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
        
        $entity_class_name = $job->entityClassName;
        $file_class = $job->owner->getFileClassName();
        
        $extension = $job->extension ?: 'xlsx';
        $filename = $this->getFilename($job);
        
        $path = sys_get_temp_dir() . '/' . $filename;
        $header = $this->getHeader($job);
        $has_sub_header = !empty($header[0]);
        $sub_header = $has_sub_header ? $header[1] : $header;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        if($has_sub_header) {
            foreach($header[0] as $col => $value) {
                $background_color = $this->getBackgroundColor();

                $last_column_index = count($header[1]) + 1;
                $last_column_letter = Coordinate::stringFromColumnIndex($last_column_index);
                $last_cell_coordinate = $last_column_letter . '2';
    
                $sheet->getStyle('A1:' . $last_cell_coordinate)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10.5],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                if($col_init = strstr($col, ':', true)) {
                    // Cor para as células e merge das células
                    $col_end = strstr($col, ':', false);
                    $letter = substr($col_end, 1, 1);
                    $number = substr($col_end, 2);
                    $new_number = (int)$number+1;
                    $total_cells = $col_init . ':' . $letter . $new_number;

                    $sheet->getStyle($total_cells)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID, 
                            'startColor' => ['rgb' => $background_color]
                        ],
                    ]);

                    $sheet->mergeCells($col);
                    $sheet->setCellValue($col_init, $value);
                    continue;
                }

                // Cor para as células e merge das células
                $letter = substr($col, 0, 1);
                $number = substr($col, 1);
                $new_number = (int)$number+1;
                $total_cells = $col . ':' . $letter . $new_number;

                $sheet->getStyle($total_cells)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID, 
                        'startColor' => ['rgb' => $background_color]
                    ],
                ]);

                $sheet->setCellValue($col, $value);
            }
        }

        $sheet->fromArray($sub_header, null, $has_sub_header ? "A2" : "A1");
        
        $row = $has_sub_header ? count($header)+1 : 2;
        while($batch = $this->getBatch($job)) {
            foreach ($batch as $data) {
                $new_data = [];
                foreach($sub_header as $prop => $label) {
                    if (isset($data[$prop]) && is_array($data[$prop])) {
                        $new_data[] = implode(', ', $data[$prop]);
                    } else {
                        $new_data[] = isset($data[$prop]) ? $data[$prop] : null; 
                    }
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
        
        $file->private = true;
        $file->group = $this->fileGroup;
        $file->owner = $job->owner;
        $file->save(true);
        
        // Disparo de e-mail
       $this->mailNotification($job->authenticatedUser, $file, $entity_class_name);
    
       return true;
    }

    function mailNotification($user, $file, $entity_class)
    {
        $app = App::i();
        
        $template = 'export_spreadsheet';
        $data = [
            'userName' => $user->profile->name,
            'pathFile' => $file->url
        ];

        $message = $app->renderMailerTemplate($template, $data);

        $app->createAndSendMailMessage([
            'from' => $app->config['mailer.from'],
            'to' => $user->email,
            'subject' => sprintf(i::__($message['title'], $entity_class)),
            'body' => $message['body'],
            //'attach' => $file->path ?? null
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

    function getBackgroundColor() {
        $colors = [
            'CCCCFF', 
            'CCFFCC', 
            'FFAAAA', 
            'BB8888', 
            '00AA00',
            'EEEEEE',
            '99D6FF',
            'FFCC99',
            'FFD700',
            'E6E6FA',
            'F0E68C',
            'FFE4B5',
            'FFE4E1'
        ];
    
        $color = array_rand($colors);
    
        return $colors[$color];
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
