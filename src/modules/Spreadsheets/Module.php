<?php

namespace Spreadsheets;

use MapasCulturais\App;
use DateTimeInterface;

class Module extends \MapasCulturais\Module {

    function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    function _init(){

        /** @var App $app */
        $app = App::i();
    }

    function register(){
        $app = App::i();
        
        $controllers = $app->getRegisteredControllers();
        if (!isset($controllers['spreadsheets'])) {
            $app->registerController('spreadsheets', Controller::class);
        }

        $app->registerJobType(new JobTypes\Entities('entities-spreadsheets'));  
        $app->registerJobType(new JobTypes\Registrations('registrations-spreadsheets'));  
    }

    static function sortExportedFilesByNewestFirst(array $files): array
    {
        $files = array_values($files);

        usort($files, function ($file_a, $file_b) {
            $timestamp_a = self::getExportedFileTimestamp($file_a);
            $timestamp_b = self::getExportedFileTimestamp($file_b);

            if ($timestamp_a === $timestamp_b) {
                return self::getExportedFileId($file_b) <=> self::getExportedFileId($file_a);
            }

            return $timestamp_b <=> $timestamp_a;
        });

        return $files;
    }

    private static function getExportedFileTimestamp($file): int
    {
        $timestamp = $file->createTimestamp ?? null;

        if ($timestamp instanceof DateTimeInterface) {
            return $timestamp->getTimestamp();
        }

        if (is_string($timestamp)) {
            return strtotime($timestamp) ?: 0;
        }

        return 0;
    }

    private static function getExportedFileId($file): int
    {
        return (int) ($file->id ?? 0);
    }
}
