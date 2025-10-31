<?php
namespace Tests;

use Exception;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;

class Fixtures {
    public static function getCSV($filename): array {
        $filename = __DIR__ . '/fixtures/' . $filename;
        if(!file_exists($filename)) {
            throw new Exception('Arquivo CSV não encontrado');
        }

        $reader = new CsvReader;

        $spreadsheet = $reader->load($filename);
        $content   = $spreadsheet->getActiveSheet()->toArray();

        $header = array_shift($content);

        $result = [];

        foreach($content as $item) {
            $result_item = [];
            foreach($header as $index => $key) {
                $result_item[$key] = $item[$index];
            }

            $result[] = $result_item;
        }

        return $result;
    }

    public static function getJSON($filename): array {
        $filename = __DIR__ . '/fixtures/' . $filename;
        if(!file_exists($filename)) {
            throw new Exception('Arquivo JSON não encontrado');
        }

        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Erro ao decodificar JSON: ' . json_last_error_msg());
        }

        return $data;
    }
}