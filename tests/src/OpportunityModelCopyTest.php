<?php

namespace Test;

use Doctrine\ORM\EntityNotFoundException;
use Laminas\Diactoros\Response;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationFileConfiguration;
use MapasCulturais\Exceptions\Halt;
use Tests\Abstract\TestCase;
use Tests\Builders\SealBuilder;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\AgentBuilder;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class OpportunityModelCopyTest extends TestCase
{
    use AgentBuilder,
        OpportunityBuilder,
        RequestFactory,
        UserDirector;

    function testTermsAreCopiedInBothFlows(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $source = $this->createSourceOpportunity($admin);
        $source->setTerms(['area' => ['Música', 'Teatro'], 'tag' => ['tag-de-teste']]);
        $source->save(true);

        $model = $this->generateModelFrom($source, 'Modelo com termos ' . uniqid('', true));
        $generated = $this->generateOpportunityFrom($model, $admin->profile->id);

        foreach ([$model, $generated] as $copy) {
            $terms = $copy->getTerms();
            $this->assertEqualsCanonicalizing(['Música', 'Teatro'], (array) $terms['area']);
            $this->assertEqualsCanonicalizing(['tag-de-teste'], (array) $terms['tag']);
        }
    }

    function testSealRelationsAreCopiedOnlyOnModelGeneration(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $seal = (new SealBuilder)
            ->reset($admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $source = $this->createSourceOpportunity($admin);
        $source->createSealRelation($seal, true, true);

        $this->assertCount(1, $source->getSealRelations());

        $model = $this->generateModelFrom($source, 'Modelo com selo ' . uniqid('', true));
        $modelSealRelations = $model->getSealRelations();

        $this->assertCount(1, $modelSealRelations);
        $this->assertSame($seal->id, $modelSealRelations[0]->seal->id);

        $generated = $this->generateOpportunityFrom($model, $admin->profile->id);

        $this->assertCount(0, $generated->getSealRelations());
    }

    function testOwnerEntityIsAppliedToGeneratedOpportunityAndItsPhases(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $targetAgent = $this->agentBuilder
            ->reset($admin)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $source = $this->createSourceOpportunityWithEvaluationPhase($admin);
        $model = $this->generateModelFrom($source, 'Modelo para vinculo ' . uniqid('', true));
        $generated = $this->generateOpportunityFrom($model, $targetAgent->id);

        $conn = $this->app->em->getConnection();
        $rows = $conn->fetchAllAssociative(
            'SELECT id, object_type, object_id FROM opportunity WHERE id = :id OR parent_id = :id ORDER BY id',
            ['id' => $generated->id]
        );

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertSame('MapasCulturais\Entities\Agent', $row['object_type'], "oportunidade {$row['id']}");
            $this->assertEquals($targetAgent->id, $row['object_id'], "oportunidade {$row['id']}");
        }
    }

    function testModelFlagsOnModelAndGeneratedOpportunity(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $source = $this->createSourceOpportunity($admin);
        $model = $this->generateModelFrom($source, 'Modelo para flags ' . uniqid('', true));

        $this->assertTrue((bool) $model->getMetadata('isModel'));

        $model->setMetadata('isModelPublic', '1');
        $model->save(true);

        $generated = $this->generateOpportunityFrom($model, $admin->profile->id);

        $this->assertFalse((bool) $generated->getMetadata('isModel'));
        $this->assertFalse((bool) $generated->getMetadata('isModelPublic'));
    }

    function testGeneratedNameComesFromPostAndShortDescriptionFromModel(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $source = $this->createSourceOpportunity($admin);
        $model = $this->generateModelFrom($source, 'Modelo com descricao ' . uniqid('', true), 'Descricao curta do modelo');
        $generatedName = 'Edital nomeado pelo POST ' . uniqid('', true);

        $generated = $this->generateOpportunityFrom($model, $admin->profile->id, $generatedName);

        $this->assertSame($generatedName, $generated->name);
        $this->assertSame('Descricao curta do modelo', $generated->shortDescription);
    }

    function testCopyHasNoOrphanEmptyRegistrationSteps(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $source = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Etapa um', 0)
                ->createField('campo-um', 'text', 'Campo um')
                ->createStep('Etapa dois', 1)
                ->createField('campo-dois', 'text', 'Campo dois')
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $model = $this->generateModelFrom($source, 'Modelo sem orfas ' . uniqid('', true));
        $generated = $this->generateOpportunityFrom($model, $admin->profile->id);

        $this->assertSame($this->countEmptySteps($source), $this->countEmptySteps($model));
        $this->assertSame($this->countEmptySteps($source), $this->countEmptySteps($generated));
    }

    function testPublicationPhaseIsSingleAndDatesFollowCopyMode(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $source = $this->createSourceOpportunity($admin);
        $sourceLastPhase = $this->lastPhaseOf($source);
        $sourceLastPhase->setPublishTimestamp(new \DateTime('2030-01-15 10:00'));
        $sourceLastPhase->save(true);

        $model = $this->generateModelFrom($source, 'Modelo publicacao ' . uniqid('', true));
        $modelLastPhases = $this->allLastPhasesOf($model);

        $this->assertCount(1, $modelLastPhases);
        $this->assertSame('2030-01-15 10:00', $modelLastPhases[0]->publishTimestamp->format('Y-m-d H:i'));

        $generated = $this->generateOpportunityFrom($model, $admin->profile->id);
        $generatedLastPhases = $this->allLastPhasesOf($generated);

        $this->assertCount(1, $generatedLastPhases);
        $this->assertNull($generatedLastPhases[0]->publishTimestamp);
    }

    function testConditionalReferencesAreRemappedOnGeneratedOpportunity(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $source = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Etapa condicional')
                ->createField('campo-controlador', 'select', title: 'Campo controlador', options: ['Sim', 'Não'])
                ->createField('campo-condicional', 'text', title: 'Campo condicional', field_condition: 'campo-controlador:Sim')
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $sourceControllerField = array_values(array_filter(
            $source->getRegistrationFieldConfigurations(),
            fn($field) => $field->title === 'Campo controlador'
        ))[0];

        $sourceFile = new RegistrationFileConfiguration();
        $sourceFile->owner = $source;
        $sourceFile->step = $sourceControllerField->step;
        $sourceFile->title = 'Anexo condicional';
        $sourceFile->conditional = true;
        $sourceFile->conditionalField = $sourceControllerField->fieldName;
        $sourceFile->conditionalValue = 'Sim';
        $sourceFile->save(true);

        $model = $this->generateModelFrom($source, 'Modelo condicional ' . uniqid('', true));
        $modelControllerField = array_values(array_filter(
            $model->getRegistrationFieldConfigurations(),
            fn($field) => $field->title === 'Campo controlador'
        ))[0];

        $generated = $this->generateOpportunityFrom($model, $admin->profile->id);
        $generatedFields = $generated->getRegistrationFieldConfigurations();
        $generatedFieldNames = array_map(fn($field) => $field->fieldName, $generatedFields);
        $generatedConditionalField = array_values(array_filter($generatedFields, fn($field) => $field->conditional))[0];
        $generatedConditionalFile = array_values(array_filter(
            $generated->getRegistrationFileConfigurations(),
            fn($file) => $file->conditional
        ))[0];

        $this->assertContains($generatedConditionalField->conditionalField, $generatedFieldNames);
        $this->assertContains($generatedConditionalFile->conditionalField, $generatedFieldNames);
        $this->assertNotSame($modelControllerField->fieldName, $generatedConditionalField->conditionalField);
    }

    function testContinuousFlowFlagsAreCopied(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $source = $this->createSourceOpportunity($admin);
        $source->setMetadata('isContinuousFlow', '1');
        $source->setMetadata('hasEndDate', '0');
        $source->save(true);

        $model = $this->generateModelFrom($source, 'Modelo continuo ' . uniqid('', true));
        $generated = $this->generateOpportunityFrom($model, $admin->profile->id);

        foreach ([$model, $generated] as $copy) {
            $this->assertTrue((bool) $copy->getMetadata('isContinuousFlow'));
            $this->assertFalse((bool) $copy->getMetadata('hasEndDate'));
        }
    }

    private function lastPhaseOf(Opportunity $opportunity): Opportunity
    {
        return $this->allLastPhasesOf($opportunity)[0];
    }

    /** @return Opportunity[] */
    private function allLastPhasesOf(Opportunity $opportunity): array
    {
        return array_values(array_filter(
            $this->app->repo('Opportunity')->findBy(['parent' => $opportunity], ['id' => 'ASC']),
            fn(Opportunity $phase) => (bool) $phase->getMetadata('isLastPhase')
        ));
    }

    private function countEmptySteps(Opportunity $opportunity): int
    {
        return (int) $this->app->em->getConnection()->fetchOne(
            'SELECT count(*) FROM registration_step rs
             WHERE rs.opportunity_id = :id
             AND NOT EXISTS (SELECT 1 FROM registration_field_configuration rfc WHERE rfc.step_id = rs.id)
             AND NOT EXISTS (SELECT 1 FROM registration_file_configuration rfile WHERE rfile.step_id = rs.id)',
            ['id' => $opportunity->id]
        );
    }

    private function createSourceOpportunity($owner): Opportunity
    {
        return $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $owner->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->refresh()
            ->getInstance();
    }

    private function createSourceOpportunityWithEvaluationPhase($owner): Opportunity
    {
        $builder = $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $owner->profile)
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
            ->fillRequiredProperties()
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->done();

        return $builder->refresh()->getInstance();
    }

    private function generateModelFrom(Opportunity $source, string $name, string $description = 'Modelo gerado pelo teste'): Opportunity
    {
        $app = $this->app;
        $app->request = $this->requestFactory->mapasPOST('opportunity', 'generatemodel', [$source->id], ['id' => $source->id]);
        $app->response = new Response();

        /** @var OpportunityController $controller */
        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $source->id]);
        $controller->postData = [
            'name' => $name,
            'description' => $description,
            'entityId' => $source->id,
        ];

        try {
            $controller->ALL_generatemodel();
        } catch (Halt) {
        } catch (EntityNotFoundException) {
            // a serialização da resposta falha com proxies deste fixture; a persistência já terminou
        }

        return $app->repo('Opportunity')->findOneBy(['name' => $name])->refreshed();
    }

    private function generateOpportunityFrom(Opportunity $model, int $ownerEntityId, ?string $name = null): Opportunity
    {
        $app = $this->app;
        $name ??= 'Edital gerado ' . uniqid('', true);
        $app->request = $this->requestFactory->mapasPOST('opportunity', 'generateopportunity', [$model->id], ['id' => $model->id]);
        $app->response = new Response();

        /** @var OpportunityController $controller */
        $controller = $app->controller('opportunity');
        // o controller fica cacheado no App e o cachedOwnerEntity vazaria entre gerações no mesmo processo
        (function () { $this->cachedOwnerEntity = null; })->call($controller);
        $controller->setRequestData(['id' => $model->id]);
        $controller->postData = [
            'name' => $name,
            'entityId' => $model->id,
            'objectType' => 'agent',
            'ownerEntity' => $ownerEntityId,
        ];

        try {
            $controller->ALL_generateopportunity();
        } catch (Halt) {
        } catch (EntityNotFoundException) {
            // a serialização da resposta falha com proxies deste fixture; a persistência já terminou
        }

        return $app->repo('Opportunity')->findOneBy(['name' => $name])->refreshed();
    }
}
