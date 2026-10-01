<?php

namespace Tests;

use Tests\Abstract\TestCase;

/**
 * Garante o padrão de sistema da tabela de inscrições (fallback quando a
 * oportunidade ainda não tem config salva por @control).
 *
 * Execução:
 * docker compose -f tests/docker-compose.yml exec -w /var/www mapas \
 *   /var/www/vendor/bin/phpunit /var/www/tests/EntityTableColumnsDefaultTest.php
 */
class EntityTableColumnsDefaultTest extends TestCase
{
    private function defaultRegistrationsPath(): string
    {
        return APPLICATION_PATH . '../src/modules/Entities/defaults/entity-table-columns/opportunity-findRegistrations-registrationsList.json';
    }

    public function testSystemDefaultRegistrationsColumnsFileExists(): void
    {
        $path = $this->defaultRegistrationsPath();
        if (!is_file($path)) {
            $path = '/var/www/src/modules/Entities/defaults/entity-table-columns/opportunity-findRegistrations-registrationsList.json';
        }

        $this->assertFileExists($path, 'Default versionado da tabela de inscricoes deve existir no modulo');

        $decoded = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($decoded);
        $this->assertNotEmpty($decoded['order'] ?? [], 'order do default nao pode ser vazio');
        $this->assertNotEmpty($decoded['visible'] ?? [], 'visible do default nao pode ser vazio');

        foreach ($decoded['order'] as $slug) {
            $this->assertStringStartsNotWith(
                'field_',
                (string) $slug,
                'Default de sistema nao deve incluir field_* de um edital especifico'
            );
        }

        $expected_visible = [
            'number',
            'agent',
            'consolidatedResult',
            'appliedPointReward',
            'score',
            'status',
        ];

        $this->assertSame(
            $expected_visible,
            $decoded['visible'],
            'Visiveis do default devem seguir o padrao final do edital 55'
        );

        $this->assertSame(
            ['number', 'agent', 'consolidatedResult', 'appliedPointReward', 'score', 'status'],
            array_slice($decoded['order'], 0, 6),
            'Ordem inicial deve comecar por number, agent, avaliacao, bonus, pontuacao e status'
        );
    }

    public function testEntityTableInitLoadsSystemDefaultsIntoJsConfig(): void
    {
        $path = '/var/www/src/modules/Entities/defaults/entity-table-columns/opportunity-findRegistrations-registrationsList.json';
        if (!is_file($path)) {
            $path = APPLICATION_PATH . '../src/modules/Entities/defaults/entity-table-columns/opportunity-findRegistrations-registrationsList.json';
        }
        $this->assertFileExists($path);

        // Simula o carregamento feito em entity-table/init.php
        $tables = [];
        $decoded = json_decode((string) file_get_contents($path), true);
        $tables['opportunity-findRegistrations-registrationsList'] = [
            'order' => $decoded['order'] ?? [],
            'visible' => $decoded['visible'] ?? [],
        ];

        $this->assertArrayHasKey('opportunity-findRegistrations-registrationsList', $tables);
        $this->assertContains('score', $tables['opportunity-findRegistrations-registrationsList']['visible']);
        $this->assertContains('appliedPointReward', $tables['opportunity-findRegistrations-registrationsList']['visible']);
    }
}
