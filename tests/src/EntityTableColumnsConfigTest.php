<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Padrão de colunas do entity-table:
 * - quem tem @control na entidade pode salvar o padrão do contexto
 * - usuário sem permissão não pode
 * - listagem geral (sem entityId) exige saasSuperAdmin
 *
 * Execução:
 * docker compose -f tests/docker-compose.yml exec -w /var/www mapas \
 *   /var/www/vendor/bin/phpunit /var/www/tests/EntityTableColumnsConfigTest.php
 */
class EntityTableColumnsConfigTest extends TestCase
{
    use OpportunityBuilder;
    use RequestFactory;
    use UserDirector;

    private array $created_config_files = [];

    protected function tearDown(): void
    {
        foreach ($this->created_config_files as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $this->created_config_files = [];
        parent::tearDown();
    }

    private function createOpportunityOwnedByAdmin(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        return [$admin, $opportunity->refreshed()];
    }

    private function configPath(string $table_key): string
    {
        return BASE_PATH . 'entity-table-columns/' . $table_key . '.json';
    }

    private function postSaveColumnsConfig(array $payload)
    {
        return $this->requestFactory->POST(
            controller_id: 'opportunity',
            action: 'saveTableColumnsConfig',
            url_params: [],
            payload: $payload
        );
    }

    private function samplePayload(Opportunity $opportunity, string $table_key): array
    {
        return [
            'tableKey' => $table_key,
            'entityType' => 'opportunity',
            'entityId' => $opportunity->id,
            'order' => ['number', 'status', 'agent', 'score'],
            'visible' => ['number', 'status', 'agent', 'score'],
            'required' => ['number'],
            'known' => ['number', 'status', 'agent', 'score', 'eligible'],
        ];
    }

    public function testOpportunityControllerCanSaveScopedColumnsConfig(): void
    {
        [$admin, $opportunity] = $this->createOpportunityOwnedByAdmin();

        $controller = $this->userDirector->createUser();
        $opportunity->createAgentRelation(
            $controller->profile,
            Agent::AGENT_RELATION_ADMIN_GROUP,
            has_control: true,
            save: true,
            flush: true
        );
        $this->processPCache();

        $table_key = 'opportunity-findRegistrations-' . $opportunity->id . '-registrationsList';
        $path = $this->configPath($table_key);
        $this->created_config_files[] = $path;
        @unlink($path);

        $this->login($controller);
        $request = $this->postSaveColumnsConfig($this->samplePayload($opportunity, $table_key));
        $this->assertStatus200($request, 'Usuário com @control deve salvar o padrão de colunas da oportunidade');

        $body = json_decode((string) App::i()->response->getBody(), true);
        $this->assertFalse($body['error'] ?? true, 'Resposta deve indicar sucesso');
        $this->assertSame($table_key, $body['tableKey'] ?? null);
        $this->assertSame(['number', 'status', 'agent', 'score'], $body['config']['visible'] ?? null);
        $this->assertSame(
            ['number', 'status', 'agent', 'score'],
            array_values(array_intersect($body['config']['order'] ?? [], ['number', 'status', 'agent', 'score'])),
            'Ordem salva deve preservar a sequência enviada das colunas visíveis'
        );
        $this->assertContains('eligible', $body['config']['order'] ?? [], 'Colunas known restantes entram no fim da ordem');

        $this->assertFileExists($path, 'Arquivo JSON do padrão deve ser persistido');
        $saved = json_decode((string) file_get_contents($path), true);
        $this->assertSame(['number', 'status', 'agent', 'score'], $saved['visible'] ?? null);
    }

    public function testUserWithoutControlCannotSaveColumnsConfig(): void
    {
        [, $opportunity] = $this->createOpportunityOwnedByAdmin();

        $outsider = $this->userDirector->createUser();
        $table_key = 'opportunity-findRegistrations-' . $opportunity->id . '-registrationsList';
        $path = $this->configPath($table_key);
        $this->created_config_files[] = $path;
        @unlink($path);

        $this->login($outsider);
        $request = $this->postSaveColumnsConfig($this->samplePayload($opportunity, $table_key));
        $this->assertStatus403($request, 'Usuário sem @control não pode salvar padrão de colunas');
        $this->assertFileDoesNotExist($path, 'Arquivo não deve ser criado sem permissão');
    }

    public function testGeneralTableWithoutEntityRequiresSaasSuperAdmin(): void
    {
        $normal = $this->userDirector->createUser();
        $this->login($normal);

        $table_key = 'agent-find-agentTable';
        $path = $this->configPath($table_key);
        $this->created_config_files[] = $path;
        @unlink($path);

        $payload = [
            'tableKey' => $table_key,
            'entityType' => null,
            'entityId' => null,
            'order' => ['name', 'type'],
            'visible' => ['name', 'type'],
            'required' => ['name'],
            'known' => ['name', 'type'],
        ];

        $request = $this->postSaveColumnsConfig($payload);
        $this->assertStatus403($request, 'Listagem geral sem entidade exige saasSuperAdmin');
        $this->assertFileDoesNotExist($path);
    }

    public function testSaasSuperAdminCanSaveGeneralTableColumnsConfig(): void
    {
        $saas = $this->userDirector->createUser('saasSuperAdmin');
        $this->login($saas);

        $table_key = 'agent-find-agentTable-test-' . uniqid();
        $path = $this->configPath($table_key);
        $this->created_config_files[] = $path;
        @unlink($path);

        $payload = [
            'tableKey' => $table_key,
            'entityType' => null,
            'entityId' => null,
            'order' => ['name', 'type'],
            'visible' => ['name', 'type'],
            'required' => ['name'],
            'known' => ['name', 'type'],
        ];

        $request = $this->postSaveColumnsConfig($payload);
        $this->assertStatus200($request, 'saasSuperAdmin deve salvar padrão de listagem geral');
        $this->assertFileExists($path);
    }
}
