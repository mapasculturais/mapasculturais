<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Exceptions\Halt;
use MapasCulturais\i;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class OpportunityAppealPhaseNameTest extends TestCase
{
    use OpportunityBuilder, RequestFactory, UserDirector;

    public static function evaluationPositions(): array
    {
        return ['primeira avaliação' => [false], 'avaliação posterior' => [true]];
    }

    #[DataProvider('evaluationPositions')]
    public function testRenamingEvaluationUpdatesPersistedAppealName(bool $secondEvaluation): void
    {
        $parent = $this->createParentPhase(secondEvaluation: $secondEvaluation);
        $appeal = $this->createAppealPhase($parent);
        $parentName = $parent->name;

        foreach (["Análise de mérito — edição d'arte", 'Habilitação documental'] as $name) {
            $parent->evaluationMethodConfiguration->name = $name;
            $parent->evaluationMethodConfiguration->save(true);

            $this->assertSame('Recurso para ' . $name, $this->persistedName($appeal));
            $this->assertSame($parentName, $this->persistedName($parent), 'Preserva o nome da coleta de dados');
        }

        $appealId = $appeal->id;
        $this->app->em->clear();
        $reloaded = $this->app->repo('Opportunity')->find($appealId);
        $this->assertSame('Recurso para Habilitação documental', $reloaded->jsonSerialize()['name']);
    }

    public function testSavingWithoutExplicitFlushKeepsPersistedNamesConsistent(): void
    {
        $parent = $this->createParentPhase();
        $appeal = $this->createAppealPhase($parent);

        $parent->evaluationMethodConfiguration->name = 'Avaliação renomeada';
        $parent->evaluationMethodConfiguration->save(false);

        // Os hooks de agendamento existentes podem executar flush mesmo em save(false).
        $persistedEvaluationName = $this->app->conn->fetchOne(
            'SELECT name FROM evaluation_method_configuration WHERE id = ?',
            [$parent->evaluationMethodConfiguration->id]
        );
        $this->assertSame('Recurso para ' . $persistedEvaluationName, $this->persistedName($appeal));
        $this->app->em->flush();
        $this->assertSame('Recurso para Avaliação renomeada', $this->persistedName($appeal));
    }

    public function testRenamingCollectionWithoutEvaluationUpdatesAppealName(): void
    {
        $parent = $this->createParentPhase(withEvaluation: false);
        $appeal = $this->createAppealPhase($parent);

        $parent->name = 'Documentação complementar';
        $parent->save(true);

        $this->assertSame('Recurso para Documentação complementar', $this->persistedName($appeal));
    }

    public function testRenamingCollectionKeepsEvaluationAsNameSource(): void
    {
        $parent = $this->createParentPhase();
        $appeal = $this->createAppealPhase($parent);
        $originalName = $this->persistedName($appeal);

        $parent->name = 'Edital renomeado';
        $parent->save(true);

        $this->assertSame($originalName, $this->persistedName($appeal));
    }

    public function testRenamingEvaluationPreservesCustomAppealName(): void
    {
        $parent = $this->createParentPhase();
        $appeal = $this->createAppealPhase($parent);
        $appeal->name = 'Revisão administrativa especial';
        $appeal->save(true);

        $parent->evaluationMethodConfiguration->name = 'Avaliação renomeada';
        $parent->evaluationMethodConfiguration->save(true);

        $this->assertSame('Revisão administrativa especial', $this->persistedName($appeal));
    }

    public function testRenamingEvaluationDoesNotCreateMissingAppeal(): void
    {
        $parent = $this->createParentPhase();
        $parent->evaluationMethodConfiguration->name = 'Avaliação renomeada';
        $parent->evaluationMethodConfiguration->save(true);

        $this->assertNull($parent->appealPhase);
        $this->assertSame(0, (int) $this->app->conn->fetchOne(
            'SELECT COUNT(*) FROM opportunity WHERE parent_id = ? AND status = ?',
            [$parent->id, Opportunity::STATUS_APPEAL_PHASE]
        ));
    }

    public function testRenamingAppealEvaluationDoesNotRenameItsOwnPhase(): void
    {
        $parent = $this->createParentPhase();
        $appeal = $this->createAppealPhase($parent);
        $originalName = $this->persistedName($appeal);

        $appeal->evaluationMethodConfiguration->name = 'Resposta do recurso';
        $appeal->evaluationMethodConfiguration->save(true);

        $this->assertSame($originalName, $this->persistedName($appeal));
    }

    public function testCreatingNextEvaluationDoesNotRenamePreviousAppeal(): void
    {
        $parent = $this->createParentPhase();
        $appeal = $this->createAppealPhase($parent);
        $originalName = $this->persistedName($appeal);

        $this->opportunityBuilder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save();

        $this->assertSame($originalName, $this->persistedName($appeal));
    }

    public function testSavingUnchangedEvaluationDoesNotSaveAppealAgain(): void
    {
        $parent = $this->createParentPhase();
        $appeal = $this->createAppealPhase($parent);
        $appealId = $appeal->id;
        $saves = 0;
        $this->app->hook('entity(Opportunity).save:before', function () use ($appealId, &$saves) {
            if ($this->id === $appealId) {
                $saves++;
            }
        });

        $parent->evaluationMethodConfiguration->save(true);

        $this->assertSame(0, $saves);
    }

    public function testRenamingEvaluationDoesNotRunAppealSaveHooks(): void
    {
        $parent = $this->createParentPhase(secondEvaluation: true);
        $appeal = $this->createAppealPhase($parent);
        $appeal->registrationFrom = new \DateTime('+2 days');
        $appeal->registrationTo = new \DateTime('+3 days');
        $appeal->save(true);
        $appealId = $appeal->id;
        $saves = 0;
        $this->app->hook('entity(Opportunity).save:before', function () use ($appealId, &$saves) {
            if ($this->id === $appealId) {
                $saves++;
            }
        });

        $parent->evaluationMethodConfiguration->name = 'Avaliação renomeada';
        $parent->evaluationMethodConfiguration->save(true);

        $this->assertSame('Recurso para Avaliação renomeada', $this->persistedName($appeal));
        $this->assertSame(0, $saves, 'Não dispara salvamento e reagendamento de jobs do recurso');
    }

    public function testLongEvaluationTitleFitsAppealNameColumn(): void
    {
        $parent = $this->createParentPhase();
        $appeal = $this->createAppealPhase($parent);
        $parent->evaluationMethodConfiguration->name = str_repeat('á', 250);
        $parent->evaluationMethodConfiguration->save(true);

        $this->assertSame(mb_substr('Recurso para ' . str_repeat('á', 250), 0, 255), $this->persistedName($appeal));
    }

    public static function historicalNames(): array
    {
        return [
            'formato atual' => ['Recurso para Avaliação antiga', 'Recurso para Avaliação técnica', 'pt_BR'],
            'formato legado' => ['Fase de recurso para Avaliação antiga', 'Fase de recurso para Avaliação técnica', 'pt_BR'],
            'já correto' => ['Recurso para Avaliação técnica', 'Recurso para Avaliação técnica', 'pt_BR'],
            'personalizado' => ['Revisão administrativa especial', 'Revisão administrativa especial', 'pt_BR'],
            'inglês' => ['Resource for Old evaluation', 'Resource for Avaliação técnica', 'en_US'],
            'espanhol' => ['Reclamo para Evaluación anterior', 'Reclamo para Avaliação técnica', 'es_ES'],
            'português com tradução ativa em inglês' => ['Recurso para Avaliação antiga', 'Recurso para Avaliação técnica', 'en_US'],
        ];
    }

    #[DataProvider('historicalNames')]
    public function testBackfillUpdatesOnlyAutomaticNameAndIsIdempotent(string $oldName, string $expectedName, string $locale): void
    {
        $parent = $this->createParentPhase(secondEvaluation: true);
        $appeal = $this->createAppealPhase($parent);
        // Simula dados gravados antes da sincronização, sem executar os novos hooks.
        $this->app->conn->executeStatement('UPDATE opportunity SET name = ? WHERE id = ?', [$oldName, $appeal->id]);
        $before = $this->app->conn->fetchAssociative('SELECT * FROM opportunity WHERE id = ?', [$appeal->id]);
        $beforePosition = $this->rowPosition($appeal);

        i::load_default_textdomain($locale);
        try {
            $this->runNameMigration();
            $after = $this->app->conn->fetchAssociative('SELECT * FROM opportunity WHERE id = ?', [$appeal->id]);
            $this->assertSame($expectedName, $after['name']);
            unset($before['name'], $after['name']);
            $this->assertSame($before, $after, 'Preserva datas, status, vínculos e demais colunas');

            $afterPosition = $this->rowPosition($appeal);
            if ($oldName === $expectedName) {
                $this->assertSame($beforePosition, $afterPosition, 'Não regrava títulos corretos ou personalizados');
            }

            $this->runNameMigration();
            $this->assertSame($expectedName, $this->persistedName($appeal));
            $this->assertSame($afterPosition, $this->rowPosition($appeal), 'A segunda execução não regrava a linha');
        } finally {
            i::load_default_textdomain('pt_BR');
        }
    }

    public function testBackfillUsesCollectionNameWhenThereIsNoEvaluation(): void
    {
        $parent = $this->createParentPhase(withEvaluation: false);
        $appeal = $this->createAppealPhase($parent);
        $this->app->conn->executeStatement('UPDATE opportunity SET name = ? WHERE id = ?', ['Coleta atualizada', $parent->id]);

        $this->runNameMigration();

        $this->assertSame('Recurso para Coleta atualizada', $this->persistedName($appeal));
    }

    public function testBackfillDoesNotRenameOrdinaryPhasesWithAppealPrefix(): void
    {
        $parent = $this->createParentPhase();
        $name = 'Recurso para outro assunto';
        $this->app->conn->executeStatement('UPDATE opportunity SET name = ? WHERE id = ?', [$name, $parent->id]);
        $position = $this->rowPosition($parent);

        $this->runNameMigration();

        $this->assertSame($name, $this->persistedName($parent));
        $this->assertSame($position, $this->rowPosition($parent));
    }

    public function testBackfillDoesNotRunAppealSaveHooks(): void
    {
        $parent = $this->createParentPhase();
        $appeal = $this->createAppealPhase($parent);
        $this->app->conn->executeStatement('UPDATE opportunity SET name = ? WHERE id = ?', ['Recurso para título antigo', $appeal->id]);
        $appealId = $appeal->id;
        $saves = 0;
        $this->app->hook('entity(Opportunity).save:before', function () use ($appealId, &$saves) {
            if ($this->id === $appealId) {
                $saves++;
            }
        });

        $this->runNameMigration();

        $this->assertSame('Recurso para Avaliação técnica', $this->persistedName($appeal));
        $this->assertSame(0, $saves);
    }

    private function runNameMigration(): void
    {
        static $updates;

        // Carrega o registro geral no contexto do App, como o atualizador do sistema.
        $updates ??= (function () {
            return require APPLICATION_PATH . 'db-updates.php';
        })->call($this->app);

        $this->assertArrayHasKey('atualiza nomes automáticos das fases de recurso', $updates);
        $this->assertTrue($updates['atualiza nomes automáticos das fases de recurso']());
    }

    private function rowPosition(Opportunity $phase): string
    {
        return $this->app->conn->fetchOne('SELECT ctid::text FROM opportunity WHERE id = ?', [$phase->id]);
    }

    private function createParentPhase(bool $withEvaluation = true, bool $secondEvaluation = false): Opportunity
    {
        $owner = $this->userDirector->createUser();
        $this->login($owner);

        $builder = $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $owner->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save();

        if (!$withEvaluation) {
            return $builder->getInstance();
        }

        $evaluation = $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->getInstance();

        if ($secondEvaluation) {
            $evaluation = $builder->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->getInstance();
        }

        $evaluation->name = 'Avaliação técnica';
        $evaluation->save(true);
        return $evaluation->opportunity;
    }

    private function createAppealPhase(Opportunity $parent): Opportunity
    {
        $this->app->request = $this->requestFactory->mapasPOST('opportunity', 'createAppealPhase', [$parent->id], ['id' => $parent->id]);
        $this->app->response = new Response();
        $controller = $this->app->controller('opportunity');
        $controller->setRequestData(['id' => $parent->id]);

        try {
            $controller->callAction('POST', 'createAppealPhase', []);
        } catch (Halt) {
        }

        $this->assertInstanceOf(Opportunity::class, $parent->appealPhase);
        return $parent->appealPhase;
    }

    private function persistedName(Opportunity $phase): string
    {
        return $this->app->conn->fetchOne('SELECT name FROM opportunity WHERE id = ?', [$phase->id]);
    }
}
