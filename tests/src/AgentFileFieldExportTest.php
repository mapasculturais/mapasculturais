<?php

namespace Tests;

use Spreadsheets\JobTypes\Registrations;
use Tests\Abstract\TestCase;

/**
 * Regressão: anexos type=file do agente (cpfAnexo, cnhAnexo, etc.)
 * não podem ser exportados/listados como [object Object] ou implode de keys.
 */
class AgentFileFieldExportTest extends TestCase
{
    private Registrations $job;

    protected function setUp(): void
    {
        parent::setUp();
        $this->job = new Registrations('registrations-spreadsheets');
    }

    public function testExtractAgentFileUrlFromArray(): void
    {
        $url = $this->job->extractAgentFileUrl([
            'id' => 42,
            'name' => 'cpf.pdf',
            'url' => 'http://localhost/files/cpf.pdf',
            'mimeType' => 'application/pdf',
        ]);

        $this->assertSame('http://localhost/files/cpf.pdf', $url);
    }

    public function testExtractAgentFileUrlFromStdClass(): void
    {
        $url = $this->job->extractAgentFileUrl((object) [
            'id' => 7,
            'name' => 'cnh.pdf',
            'url' => 'https://example.test/cnh.pdf',
            'mimeType' => 'application/pdf',
        ]);

        $this->assertSame('https://example.test/cnh.pdf', $url);
    }

    public function testExtractAgentFileUrlReturnsNullForScalars(): void
    {
        $this->assertNull($this->job->extractAgentFileUrl(null));
        $this->assertNull($this->job->extractAgentFileUrl(''));
        $this->assertNull($this->job->extractAgentFileUrl('texto'));
        $this->assertNull($this->job->extractAgentFileUrl(123));
    }

    public function testExtractAgentFileUrlReturnsNullForListArrays(): void
    {
        $this->assertNull($this->job->extractAgentFileUrl(['a', 'b']));
        $this->assertNull($this->job->extractAgentFileUrl([
            ['url' => 'http://x', 'name' => 'a'],
        ]));
    }

    public function testExtractAgentFileUrlReturnsNullWithoutUrlOrMarkers(): void
    {
        $this->assertNull($this->job->extractAgentFileUrl([
            'foo' => 'bar',
            'baz' => 1,
        ]));
        $this->assertNull($this->job->extractAgentFileUrl([
            'url' => 'http://localhost/x',
        ]));
        $this->assertNull($this->job->extractAgentFileUrl([
            'name' => 'file.pdf',
            'id' => 1,
        ]));
    }
}
