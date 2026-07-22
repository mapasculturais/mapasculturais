<?php

namespace Test;

use DateTime;
use Spreadsheets\Module;
use Tests\Abstract\TestCase;

class SpreadsheetExportsTest extends TestCase
{
    function testExportedFilesAreSortedByNewestFirst(): void
    {
        $older = (object) [
            'id' => 10,
            'createTimestamp' => new DateTime('2026-04-30 01:41:10'),
        ];
        $newer = (object) [
            'id' => 20,
            'createTimestamp' => new DateTime('2026-06-03 15:45:50'),
        ];
        $sameTimestampHigherId = (object) [
            'id' => 30,
            'createTimestamp' => new DateTime('2026-06-03 15:45:50'),
        ];

        $sorted = Module::sortExportedFilesByNewestFirst([
            $older,
            $newer,
            $sameTimestampHigherId,
        ]);

        $this->assertSame(
            [30, 20, 10],
            array_map(fn($file) => $file->id, $sorted),
            'Garantindo que os arquivos exportados sejam listados do mais recente para o mais antigo'
        );
    }
}
