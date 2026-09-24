<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Exceptions\Halt;
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
