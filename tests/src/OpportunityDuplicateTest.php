<?php

namespace Test;

use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class OpportunityDuplicateTest extends TestCase
{
    use OpportunityBuilder,
        RequestFactory,
        UserDirector;

    function testDuplicatingOpportunityWithEvaluationOnFirstPhaseDoesNotCreateExtraPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save();

        $builder->addEvaluationPhase(EvaluationMethods::technical)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->done();

        $builder->addDataCollectionPhase()
            ->setRegistrationPeriod(new Open)
            ->save()
            ->done();

        $source = $builder->refresh()->getInstance();

        $this->assertNotNull($source->evaluationMethodConfiguration, 'Garantindo que a avaliação da origem está na primeira fase');

        $duplicated = $this->duplicate($source);

        $this->assertSame(
            $this->describePhases($source),
            $this->describePhases($duplicated),
            'Certificando que a cópia mantém a mesma estrutura de fases da origem'
        );
        $this->assertNotNull($duplicated->evaluationMethodConfiguration, 'Certificando que a avaliação duplicada fica na primeira fase da cópia');
        $this->assertNotEquals(
            $source->evaluationMethodConfiguration->id,
            $duplicated->evaluationMethodConfiguration->id,
            'Certificando que a cópia tem sua própria configuração de avaliação'
        );
    }

    function testDuplicatingOpportunityWithEvaluationOnLaterPhaseDoesNotCreateExtraPhase(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save();

        $builder->addDataCollectionPhase()
            ->setRegistrationPeriod(new Open)
            ->save()
            ->done();

        $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->done();

        $source = $builder->refresh()->getInstance();

        $duplicated = $this->duplicate($source);

        $this->assertSame(
            $this->describePhases($source),
            $this->describePhases($duplicated),
            'Certificando que a cópia mantém a mesma estrutura de fases da origem'
        );
    }

    private function duplicate(Opportunity $source): Opportunity
    {
        $source->name = 'Edital duplicado ' . uniqid('', true);
        $source->save(true);

        $request = $this->requestFactory->POST(
            controller_id: 'opportunity',
            action: 'duplicate',
            url_params: [$source->id],
            ajax: true
        );
        $this->assertStatus200($request, 'Garantindo status 200 ao duplicar oportunidade');

        $this->app->em->clear();

        $duplicated = $this->app->em->createQuery("
            SELECT o
            FROM MapasCulturais\Entities\Opportunity o
            WHERE o.parent IS NULL AND o.name LIKE :name
        ")
            ->setParameter('name', $source->name . '%[Cópia]%')
            ->getOneOrNullResult();

        $this->assertNotNull($duplicated, 'Certificando que a oportunidade duplicada foi encontrada');

        return $duplicated;
    }

    /**
     * Sequência de fases como [é coleta de dados?, tipo de avaliação|null], ignorando nomes e datas.
     */
    private function describePhases(Opportunity $opportunity): array
    {
        $opportunity = $opportunity->refreshed();

        return array_map(
            static fn(Opportunity $phase) => [
                (bool) $phase->isDataCollection,
                $phase->evaluationMethodConfiguration?->type?->id,
            ],
            $opportunity->allPhases
        );
    }
}
