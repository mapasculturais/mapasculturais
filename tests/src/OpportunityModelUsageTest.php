<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Definitions\Metadata;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Exceptions\Halt;
use MapasCulturais\Exceptions\PermissionDenied;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class OpportunityModelUsageTest extends TestCase
{
    use OpportunityBuilder,
        RequestFactory,
        UserDirector;

    function testPublicModelCanBeUsedByAnotherUser(): void
    {
        $modelOwner = $this->userDirector->createUser();
        $user = $this->userDirector->createUser();
        $model = $this->createModel($modelOwner, true);

        $this->login($user);
        $generated = $this->generateOpportunity($model, $user->profile->id);

        $this->assertSame($user->profile->id, $generated->ownerEntity->id);
    }

    function testPrivateModelCannotBeUsedByAnotherUser(): void
    {
        $modelOwner = $this->userDirector->createUser();
        $user = $this->userDirector->createUser();
        $model = $this->createModel($modelOwner, false);

        $this->login($user);

        $this->expectException(PermissionDenied::class);
        $this->generateOpportunity($model, $user->profile->id);
    }

    function testGeneratedOpportunityCannotBeLinkedToEntityWithoutControl(): void
    {
        $modelOwner = $this->userDirector->createUser();
        $user = $this->userDirector->createUser();
        $otherUser = $this->userDirector->createUser();
        $model = $this->createModel($modelOwner, true);

        $this->login($user);

        $this->expectException(PermissionDenied::class);
        $this->generateOpportunity($model, $otherUser->profile->id);
    }

    function testAccessControlIsRestoredWhenGenerationFails(): void
    {
        $modelOwner = $this->userDirector->createUser();
        $user = $this->userDirector->createUser();
        $model = $this->createModel($modelOwner, true);
        $generatedName = 'Uso de modelo com falha ' . uniqid('', true);

        $this->app->hook('entity(Opportunity).insert:after', function () use ($generatedName) {
            if ($this->name === $generatedName) {
                throw new \RuntimeException('Falha simulada durante a geração');
            }
        });

        $this->login($user);

        try {
            $this->generateOpportunity($model, $user->profile->id, $generatedName);
            $this->fail('Garantindo que a falha simulada interrompa a geração');
        } catch (\RuntimeException) {
        }

        $this->assertTrue($this->app->isAccessControlEnabled());
    }

    function testEvaluationMethodMetadataIsCopiedWithBatchedSaves(): void
    {
        $owner = $this->userDirector->createUser();
        $model = $this->createModel($owner, true);
        $generatedName = 'Uso de modelo otimizado ' . uniqid('', true);
        $metadata = [
            'modelUsagePerformanceA' => 'valor A',
            'modelUsagePerformanceB' => 'valor B',
            'modelUsagePerformanceC' => 'valor C',
        ];

        foreach ($metadata as $key => $value) {
            $this->app->registerMetadata(
                new Metadata($key, ['label' => $key, 'type' => 'text']),
                EvaluationMethodConfiguration::class,
                'simple'
            );
        }

        $configuration = new EvaluationMethodConfiguration();
        $configuration->opportunity = $model;
        $configuration->type = 'simple';
        $configuration->name = 'Avaliação do modelo';
        $configuration->save(true);

        foreach ($metadata as $key => $value) {
            $configuration->setMetadata($key, $value);
        }
        $configuration->save(true);

        $generatedConfigurationSaveCount = 0;
        $this->app->hook('entity(EvaluationMethodConfiguration).save:finish', function () use ($generatedName, &$generatedConfigurationSaveCount) {
            if ($this->opportunity->name === $generatedName) {
                $generatedConfigurationSaveCount++;
            }
        });

        $generated = $this->generateOpportunity($model, $owner->profile->id, $generatedName);
        $generatedConfiguration = $this->app->repo('EvaluationMethodConfiguration')->findOneBy([
            'opportunity' => $generated,
        ]);

        $this->assertSame(2, $generatedConfigurationSaveCount);
        foreach ($metadata as $key => $value) {
            $this->assertSame($value, $generatedConfiguration->getMetadata($key));
        }
    }

    function testUsingPublicGeneratedModelDoesNotCreateExtraDataCollectionPhase(): void
    {
        $modelOwner = $this->userDirector->createUser();
        $user = $this->userDirector->createUser();
        $modelName = 'Modelo publico sem fase extra ' . uniqid('', true);

        $this->login($modelOwner);
        $builder = $this->opportunityBuilder
            ->reset(owner: $modelOwner->profile, owner_entity: $modelOwner->profile)
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

        $source = $builder
            ->refresh()
            ->getInstance();

        $model = $this->generateModelFromOpportunity($source, $modelName);
        $model->setMetadata('isModelPublic', 1);
        $model->save(true);
        $model = $model->refreshed();

        $this->login($user);
        $generated = $this->generateOpportunity($model, $user->profile->id)->refreshed();

        $this->assertCount(count($model->allPhases), $generated->allPhases);
        $this->assertSame(
            $this->countDataCollectionPhases($model),
            $this->countDataCollectionPhases($generated)
        );
    }

    private function createModel($owner, bool $isPublic): Opportunity
    {
        $this->login($owner);

        $model = $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $owner->profile)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();

        $model->setMetadata('isModel', 1);
        $model->setMetadata('isModelPublic', $isPublic ? 1 : 0);
        $model->save(true);

        return $model;
    }

    private function generateModelFromOpportunity(Opportunity $source, string $name): Opportunity
    {
        $app = $this->app;
        $app->request = $this->requestFactory->mapasPOST('opportunity', 'generatemodel', [$source->id], ['id' => $source->id]);
        $app->response = new Response();

        /** @var OpportunityController $controller */
        $controller = $app->controller('opportunity');
        $controller->setRequestData(['id' => $source->id]);
        $controller->postData = [
            'name' => $name,
            'description' => 'Modelo publico gerado pelo teste',
            'entityId' => $source->id,
        ];

        try {
            $controller->ALL_generatemodel();
        } catch (Halt) {
        }

        return $app->repo('Opportunity')->findOneBy(['name' => $name])->refreshed();
    }

    private function generateOpportunity(Opportunity $model, int $ownerEntityId, ?string $name = null): Opportunity
    {
        $app = $this->app;
        $name ??= 'Uso de modelo ' . uniqid('', true);
        $app->request = $this->requestFactory->mapasPOST('opportunity', 'generateopportunity', [$model->id], ['id' => $model->id]);
        $app->response = new Response();

        /** @var OpportunityController $controller */
        $controller = $app->controller('opportunity');
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
        }

        return $app->repo('Opportunity')->findOneBy(['name' => $name])->refreshed();
    }

    private function countDataCollectionPhases(Opportunity $opportunity): int
    {
        return count(array_filter(
            $opportunity->allPhases,
            fn(Opportunity $phase) => $phase->isDataCollection && !$phase->isLastPhase
        ));
    }
}
