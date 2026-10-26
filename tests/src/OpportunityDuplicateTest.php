<?php

namespace Test;

use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationFileConfiguration;
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

    private function createSourceWithVisibleEvaluationFields($owner): Opportunity
    {
        $source = $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $owner->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Etapa única')
                ->createField('campo-visivel', 'text', title: 'Campo visível')
                ->createField('campo-oculto', 'text', title: 'Campo oculto')
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $attachment = new RegistrationFileConfiguration();
        $attachment->owner = $source;
        $attachment->step = $this->fieldTitled($source, 'Campo visível')->step;
        $attachment->title = 'Anexo visível';
        $attachment->save(true);

        $source = $source->refreshed();

        $configuration = ['agentsSummary' => 'true'];
        foreach ($source->getRegistrationFieldConfigurations() as $field) {
            $configuration[$field->fieldName] = $field->title === 'Campo oculto' ? 'false' : 'true';
        }
        foreach ($source->getRegistrationFileConfigurations() as $file) {
            $configuration[$file->fileGroupName] = 'true';
        }

        $source->avaliableEvaluationFields = $configuration;
        $source->save(true);

        return $source->refreshed();
    }

    private function fieldTitled(Opportunity $opportunity, string $title)
    {
        return array_values(array_filter(
            $opportunity->getRegistrationFieldConfigurations(),
            fn($field) => $field->title === $title
        ))[0];
    }

    private function foreignEvaluationFieldKeys(Opportunity $opportunity): array
    {
        $own = array_map(
            fn($field) => $field->fieldName,
            $opportunity->getRegistrationFieldConfigurations()
        );
        foreach ($opportunity->getRegistrationFileConfigurations() as $file) {
            $own[] = $file->fileGroupName;
        }

        return array_values(array_filter(
            array_keys($opportunity->getStoredAvaliableEvaluationFields()),
            fn($key) => preg_match('#^(field|rfc)_\d+$#', (string) $key) && !in_array($key, $own, true)
        ));
    }

    function testEvaluationFieldsConfigurationIsRemappedOnDuplicatedOpportunity(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $source = $this->createSourceWithVisibleEvaluationFields($admin);
        $duplicated = $this->duplicate($source);

        $this->assertSame([], $this->foreignEvaluationFieldKeys($duplicated));
        $this->assertCount(4, $duplicated->getStoredAvaliableEvaluationFields());
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
