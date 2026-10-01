<?php

namespace Tests;

use Closure;
use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Entities\RegistrationFileConfiguration;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\UserDirector;

/**
 * Regressão do db-update que corrige as etapas legadas das oportunidades:
 * campos/anexos de formulário sem etapa e etapas vazias duplicadas.
 */
class RegistrationStepLegacyDbUpdateTest extends TestCase
{
    use OpportunityBuilder,
        UserDirector;

    private const UPDATE_NAME = 'Corrige dados legados das etapas de inscrição das oportunidades (campos e anexos sem etapa e etapas vazias duplicadas)';

    /** @var array<string, Closure>|null Closures do src/db-updates.php, incluído uma única vez por processo. */
    private static ?array $db_updates = null;

    /**
     * Executa a closure do db-update corrigido diretamente, sem passar pelo
     * registro da tabela db_update. O arquivo src/db-updates.php lê
     * $this->_register, por isso o include roda com escopo da classe App.
     */
    private function runDbUpdate(): void
    {
        if (self::$db_updates === null) {
            if (!defined('DB_UPDATES_FILE')) {
                // Sobe até a raiz do repositório: no container de testes o teste roda
                // de /var/www/tests (mount de tests/src), fora dele de <raiz>/tests/src.
                $dir = __DIR__;
                while ($dir !== '/' && !file_exists($dir . '/src/db-updates.php')) {
                    $dir = dirname($dir);
                }

                define('DB_UPDATES_FILE', $dir . '/src/db-updates.php');
            }

            $include = function () {
                return include DB_UPDATES_FILE;
            };

            $loader = Closure::bind($include, $this->app, App::class);
            self::$db_updates = $loader();
        }

        $this->assertArrayHasKey(self::UPDATE_NAME, self::$db_updates, 'O db-update deve estar registrado em src/db-updates.php');

        (self::$db_updates[self::UPDATE_NAME])();
    }

    /**
     * Cria uma oportunidade com campos e anexos de formulário. O hook
     * insert:after da oportunidade cria automaticamente uma etapa vazia,
     * que os testes esculpem via SQL para simular os estados legados.
     */
    private function createOpportunityWithForm(int $fields = 2, int $files = 1): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $app = $this->app;
        $app->disableAccessControl();
        try {
            for ($i = 1; $i <= $fields; $i++) {
                $field = new RegistrationFieldConfiguration();
                $field->owner = $opportunity;
                $field->title = "Campo {$i}";
                $field->fieldType = 'text';
                $field->displayOrder = $i;
                $field->save(true);
            }

            for ($i = 1; $i <= $files; $i++) {
                $file = new RegistrationFileConfiguration();
                $file->owner = $opportunity;
                $file->title = "Anexo {$i}";
                $file->displayOrder = $i;
                $file->save(true);
            }
        } finally {
            $app->enableAccessControl();
        }

        return $opportunity;
    }

    private function conn(): \Doctrine\DBAL\Connection
    {
        return $this->app->em->getConnection();
    }

    /** @return array<int, array<string, mixed>> */
    private function stepsOf(int $opportunity_id): array
    {
        return $this->conn()->fetchAllAssociative(
            'SELECT id, name, display_order FROM registration_step WHERE opportunity_id = ? ORDER BY id',
            [$opportunity_id]
        );
    }

    /** @return array<int, int|null> step_id dos campos, ordenado por id do campo. */
    private function fieldStepIds(int $opportunity_id): array
    {
        $rows = $this->conn()->fetchAllAssociative(
            'SELECT step_id FROM registration_field_configuration WHERE opportunity_id = ? ORDER BY id',
            [$opportunity_id]
        );

        return array_map(fn($row) => $row['step_id'] === null ? null : (int) $row['step_id'], $rows);
    }

    /** @return array<int, int|null> step_id dos anexos, ordenado por id do anexo. */
    private function fileStepIds(int $opportunity_id): array
    {
        $rows = $this->conn()->fetchAllAssociative(
            'SELECT step_id FROM registration_file_configuration WHERE opportunity_id = ? ORDER BY id',
            [$opportunity_id]
        );

        return array_map(fn($row) => $row['step_id'] === null ? null : (int) $row['step_id'], $rows);
    }

    private function insertStep(int $opportunity_id, string $name, int $display_order): int
    {
        $step_id = $this->conn()->executeQuery(
            "INSERT INTO registration_step (name, display_order, opportunity_id, create_timestamp, update_timestamp)
             VALUES (?, ?, ?, NOW(), NOW()) RETURNING id",
            [$name, $display_order, $opportunity_id]
        )->fetchOne();

        return (int) $step_id;
    }

    private function attachAllTo(int $opportunity_id, int $step_id): void
    {
        $this->conn()->executeStatement(
            'UPDATE registration_field_configuration SET step_id = ? WHERE opportunity_id = ?',
            [$step_id, $opportunity_id]
        );
        $this->conn()->executeStatement(
            'UPDATE registration_file_configuration SET step_id = ? WHERE opportunity_id = ?',
            [$step_id, $opportunity_id]
        );
    }

    private function detachAll(int $opportunity_id): void
    {
        $this->conn()->executeStatement(
            'UPDATE registration_field_configuration SET step_id = NULL WHERE opportunity_id = ?',
            [$opportunity_id]
        );
        $this->conn()->executeStatement(
            'UPDATE registration_file_configuration SET step_id = NULL WHERE opportunity_id = ?',
            [$opportunity_id]
        );
    }

    private function deleteSteps(int $opportunity_id): void
    {
        $this->conn()->executeStatement(
            'DELETE FROM registration_step WHERE opportunity_id = ?',
            [$opportunity_id]
        );
    }

    /** Estado completo usado para comparar antes/depois. */
    private function snapshot(int $opportunity_id): array
    {
        return [
            'steps' => $this->stepsOf($opportunity_id),
            'fields' => $this->fieldStepIds($opportunity_id),
            'files' => $this->fileStepIds($opportunity_id),
        ];
    }

    /**
     * Caso (a), oportunidade sem nenhuma etapa: o db-update cria uma única
     * etapa vazia e associa apenas os registros sem etapa.
     */
    public function testSteplessFieldsAndFilesGainASingleNewStep(): void
    {
        $opportunity = $this->createOpportunityWithForm(fields: 2, files: 1);

        // Estado legado: sem etapas e com campos/anexos sem etapa.
        $this->deleteSteps($opportunity->id);
        $this->detachAll($opportunity->id);

        $this->runDbUpdate();

        $steps = $this->stepsOf($opportunity->id);
        $this->assertCount(1, $steps, 'Deve existir exatamente uma etapa após o update');
        $this->assertSame('', $steps[0]['name']);
        $this->assertSame(0, (int) $steps[0]['display_order']);

        $step_id = (int) $steps[0]['id'];
        $this->assertSame([$step_id, $step_id], $this->fieldStepIds($opportunity->id), 'Todos os campos devem ganhar a etapa criada');
        $this->assertSame([$step_id], $this->fileStepIds($opportunity->id), 'Todos os anexos devem ganhar a etapa criada');
    }

    /**
     * Caso (a), oportunidade que já possui etapa vazia (criada pelo hook):
     * a etapa existente é reutilizada, sem criar duplicata.
     */
    public function testSteplessFieldsReuseExistingEmptyStep(): void
    {
        $opportunity = $this->createOpportunityWithForm(fields: 2, files: 1);

        // Estado legado: etapa vazia do hook + campos/anexos sem etapa.
        $hook_steps = $this->stepsOf($opportunity->id);
        $this->detachAll($opportunity->id);
        $hook_step_id = (int) $hook_steps[0]['id'];

        $this->runDbUpdate();

        $steps = $this->stepsOf($opportunity->id);
        $this->assertCount(1, $steps, 'Nenhuma etapa duplicada deve ser criada');
        $this->assertSame($hook_step_id, (int) $steps[0]['id'], 'A etapa vazia existente deve ser reutilizada');
        $this->assertSame([$hook_step_id, $hook_step_id], $this->fieldStepIds($opportunity->id));
        $this->assertSame([$hook_step_id], $this->fileStepIds($opportunity->id));
    }

    /**
     * Caso (b): edital já válido (uma etapa com todos os campos/anexos)
     * permanece inalterado.
     */
    public function testOpportunityWithValidStepIsLeftUnchanged(): void
    {
        $opportunity = $this->createOpportunityWithForm(fields: 2, files: 1);

        $valid_step_id = (int) $this->stepsOf($opportunity->id)[0]['id'];
        $this->attachAllTo($opportunity->id, $valid_step_id);

        $before = $this->snapshot($opportunity->id);

        $this->runDbUpdate();

        $this->assertSame($before, $this->snapshot($opportunity->id), 'Oportunidade válida não deve ser alterada');
    }

    /**
     * Caso (c): oportunidade com exatamente duas etapas, uma vazia e outra
     * com todos os campos/anexos, fica apenas com a etapa completa.
     */
    public function testDuplicateEmptyStepIsRemovedKeepingCompleteStep(): void
    {
        $opportunity = $this->createOpportunityWithForm(fields: 2, files: 1);

        // Estado legado: etapa vazia do hook + etapa completa do mc-update antigo.
        $this->insertStep($opportunity->id, 'Etapa completa', 1);
        $steps = $this->stepsOf($opportunity->id);
        $complete_step_id = (int) $steps[1]['id'];
        $this->attachAllTo($opportunity->id, $complete_step_id);

        $this->runDbUpdate();

        $steps = $this->stepsOf($opportunity->id);
        $this->assertCount(1, $steps, 'Apenas a etapa completa deve permanecer');
        $this->assertSame($complete_step_id, (int) $steps[0]['id'], 'A etapa removida deve ser a vazia');
        $this->assertSame([$complete_step_id, $complete_step_id], $this->fieldStepIds($opportunity->id));
        $this->assertSame([$complete_step_id], $this->fileStepIds($opportunity->id));
    }

    /**
     * Caso (d): duas etapas fora do padrão (ambas com campos/anexos) e três
     * etapas com uma vazia não são tocadas.
     */
    public function testTwoStepsOutsideThePatternAreLeftUnchanged(): void
    {
        // Padrão fora do formato: duas etapas, ambas com campos.
        $two_steps = $this->createOpportunityWithForm(fields: 2, files: 0);
        $steps = $this->stepsOf($two_steps->id);
        $first_step_id = (int) $steps[0]['id'];
        $second_step_id = $this->insertStep($two_steps->id, 'Etapa 2', 1);

        $this->conn()->executeStatement(
            'UPDATE registration_field_configuration SET step_id = ? WHERE opportunity_id = ? AND display_order = 1',
            [$first_step_id, $two_steps->id]
        );
        $this->conn()->executeStatement(
            'UPDATE registration_field_configuration SET step_id = ? WHERE opportunity_id = ? AND display_order = 2',
            [$second_step_id, $two_steps->id]
        );

        // Padrão fora do formato: três etapas com uma vazia.
        $three_steps = $this->createOpportunityWithForm(fields: 1, files: 0);
        $extra_step_id = $this->insertStep($three_steps->id, 'Etapa 2', 1);
        $this->attachAllTo($three_steps->id, $extra_step_id);
        $this->insertStep($three_steps->id, 'Etapa 3', 2);

        $before_two = $this->snapshot($two_steps->id);
        $before_three = $this->snapshot($three_steps->id);

        $this->runDbUpdate();

        $this->assertSame($before_two, $this->snapshot($two_steps->id), 'Duas etapas fora do padrão não devem ser alteradas');
        $this->assertSame($before_three, $this->snapshot($three_steps->id), 'Três etapas não devem ser alteradas');
    }

    /**
     * Caso (e): a segunda execução do db-update não altera nada.
     */
    public function testSecondExecutionChangesNothing(): void
    {
        $stepless = $this->createOpportunityWithForm(fields: 2, files: 1);
        $this->deleteSteps($stepless->id);
        $this->detachAll($stepless->id);

        $duplicated = $this->createOpportunityWithForm(fields: 2, files: 1);
        $this->insertStep($duplicated->id, 'Etapa completa', 1);
        $steps = $this->stepsOf($duplicated->id);
        $this->attachAllTo($duplicated->id, (int) $steps[1]['id']);

        $this->runDbUpdate();

        $state_after_first = [
            'stepless' => $this->snapshot($stepless->id),
            'duplicated' => $this->snapshot($duplicated->id),
        ];

        $this->runDbUpdate();

        $state_after_second = [
            'stepless' => $this->snapshot($stepless->id),
            'duplicated' => $this->snapshot($duplicated->id),
        ];

        $this->assertSame($state_after_first, $state_after_second, 'A segunda execução não deve alterar nenhum dado');
        $this->assertNotEmpty($state_after_second['stepless']['steps'], 'Sanidade: o primeiro run corrigiu o cenário 1');
        $this->assertCount(1, $state_after_second['duplicated']['steps'], 'Sanidade: o primeiro run corrigiu o cenário 2');
    }
}
