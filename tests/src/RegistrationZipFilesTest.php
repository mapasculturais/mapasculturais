<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use ZipArchive;

class RegistrationZipFilesTest extends TestCase
{
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        parent::tearDown();
    }

    private function file(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'registration-zip-test-');
        file_put_contents($path, $content);
        $this->temporaryFiles[] = $path;
        return $path;
    }

    private function scenario(): array
    {
        return [
            'phases' => [
                ['id' => 10, 'name' => 'Inscricao'],
                ['id' => 20, 'name' => 'Habilitacao'],
                ['id' => 30, 'name' => 'Termo de execucao cultural'],
            ],
            'registrations' => [
                ['id' => 101, 'number' => 'on-123', 'opportunity' => 10, 'files' => ['file_1' => $this->file('Inscricao')]],
                ['id' => 102, 'number' => 'on-123', 'opportunity' => 20, 'files' => ['file_2' => $this->file('Habilitacao')]],
                ['id' => 103, 'number' => 'on-123', 'opportunity' => 30, 'files' => ['file_3' => $this->file('Termo')]],
            ],
            'requested' => 0,
            'phaseIds' => [10, 20, 30],
        ];
    }

    private function response(array $scenario): string
    {
        $process = proc_open([PHP_BINARY, __DIR__ . '/fixtures/registration-zip.php'], [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);
        fwrite($pipes[0], json_encode($scenario, JSON_THROW_ON_ERROR));
        fclose($pipes[0]);
        $content = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertSame('', $errors);
        return $content;
    }

    private function download(array $scenario): array
    {
        $content = $this->response($scenario);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($this->file($content)), 'O endpoint deve retornar um ZIP valido');
        $entries = [];
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entries[$zip->getNameIndex($index)] = $zip->getFromIndex($index);
        }
        $zip->close();
        return $entries;
    }

    public function testIncludesAttachmentsFromEveryDataCollectionPhase(): void
    {
        $entries = $this->download($this->scenario());
        $this->assertEqualsCanonicalizing(['Inscricao', 'Habilitacao', 'Termo'], array_values($entries));
    }

    public function testDownloadsAllPhasesWhenOpenedFromALaterPhase(): void
    {
        $scenario = $this->scenario();
        $scenario['requested'] = 2;
        $this->assertEqualsCanonicalizing(['Inscricao', 'Habilitacao', 'Termo'], array_values($this->download($scenario)));
    }

    public function testIncludesEveryFileInAnAttachmentGroup(): void
    {
        $scenario = $this->scenario();
        $scenario['registrations'][0]['files']['file_1'] = [$this->file('Documento 1'), $this->file('Documento 2')];
        $this->assertEqualsCanonicalizing(
            ['Documento 1', 'Documento 2', 'Habilitacao', 'Termo'],
            array_values($this->download($scenario))
        );
    }

    public function testFindsAttachmentsWhenTheRequestedPhaseHasNoFiles(): void
    {
        $scenario = $this->scenario();
        $scenario['registrations'][0]['files'] = [];
        $this->assertEqualsCanonicalizing(['Habilitacao', 'Termo'], array_values($this->download($scenario)));
    }

    public function testDoesNotIncludeAttachmentsFromAnInaccessiblePhaseOrAnotherRegistration(): void
    {
        $scenario = $this->scenario();
        $scenario['registrations'][1]['canView'] = false;
        $scenario['registrations'][] = ['id' => 104, 'number' => 'on-456', 'opportunity' => 10, 'files' => ['file_4' => $this->file('Outra inscricao')]];
        $scenario['phases'][] = ['id' => 40, 'name' => 'Outra oportunidade'];
        $scenario['registrations'][] = ['id' => 105, 'number' => 'on-123', 'opportunity' => 40, 'files' => ['file_5' => $this->file('Outra oportunidade')]];
        $this->assertEqualsCanonicalizing(['Inscricao', 'Termo'], array_values($this->download($scenario)));
    }

    public function testSkipsPhasesWithoutDataCollection(): void
    {
        $scenario = $this->scenario();
        $scenario['phases'][1]['isDataCollection'] = false;
        $this->assertEqualsCanonicalizing(['Inscricao', 'Termo'], array_values($this->download($scenario)));
    }

    public function testKeepsTheSinglePhaseDownloadWorking(): void
    {
        $scenario = $this->scenario();
        $scenario['phases'] = [$scenario['phases'][0]];
        $scenario['registrations'] = [$scenario['registrations'][0]];
        $scenario['phaseIds'] = [10];
        $this->assertSame(['Inscricao'], array_values($this->download($scenario)));
    }

    public function testSkipsMissingFilesAndEmptyGroups(): void
    {
        $scenario = $this->scenario();
        $missing = $this->file('Removido');
        unlink($missing);
        $scenario['registrations'][0]['files']['file_4'] = $missing;
        $scenario['registrations'][1]['files']['file_5'] = [];
        $this->assertEqualsCanonicalizing(['Inscricao', 'Habilitacao', 'Termo'], array_values($this->download($scenario)));
    }

    public function testReturns400WhenNoPhaseHasAttachments(): void
    {
        $scenario = $this->scenario();
        foreach ($scenario['registrations'] as &$registration) {
            $registration['files'] = [];
        }
        unset($registration);
        $response = json_decode($this->response($scenario), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(400, $response['status']);
        $this->assertFalse($response['data']['success']);
    }

    public function testReturns400WhenOnlyInaccessiblePhasesHaveAttachments(): void
    {
        $scenario = $this->scenario();
        $scenario['registrations'][0]['files'] = [];
        $scenario['registrations'][1]['canView'] = false;
        $scenario['registrations'][2]['canView'] = false;
        $response = json_decode($this->response($scenario), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(400, $response['status']);
        $this->assertFalse($response['data']['success']);
    }

    public function testPreservesFilesWithTheSameNameInDifferentPhases(): void
    {
        $scenario = $this->scenario();
        $original = $scenario['registrations'][0]['files']['file_1'];
        $copy = sys_get_temp_dir() . '/' . basename($original) . '-copy';
        mkdir($copy);
        $sameName = $copy . '/' . basename($original);
        file_put_contents($sameName, 'Habilitacao');
        $this->temporaryFiles[] = $sameName;
        $scenario['registrations'][1]['files']['file_2'] = $sameName;
        try {
            $entries = $this->download($scenario);
            $this->assertEqualsCanonicalizing(['Inscricao', 'Habilitacao', 'Termo'], array_values($entries));
        } finally {
            unlink($sameName);
            rmdir($copy);
        }
    }
}
