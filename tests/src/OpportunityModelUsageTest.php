<?php

namespace Test;

use Doctrine\ORM\EntityNotFoundException;
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

    function testOpportunityGeneratedFromModelDoesNotCopyPhaseDates(): void
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

        $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->setAutoPublish()
            ->save()
            ->done();

        $additionalPhase = $builder->addDataCollectionPhase()
            ->setRegistrationPeriod(new Open)
            ->getInstance();
        $additionalPhase->publishTimestamp = new \DateTime('+ 1 month');
        $additionalPhase->autoPublish = true;
        $additionalPhase->save(true);

        $source = $builder->refresh()->getInstance();
        $source->lastPhase->publishTimestamp = new \DateTime('+ 2 months');
        $source->lastPhase->save(true);

        $model = $this->generateModelFromOpportunity(
            $source,
            'Modelo com datas ' . uniqid('', true)
        );
        $model->setMetadata('isModelPublic', 1);
        $model->save(true);
        $model = $model->refreshed();

        $this->assertSame($this->getPhaseDates($source->refreshed()), $this->getPhaseDates($model));

        $model->lastPhase->autoPublish = true;
        $model->lastPhase->save(true);
        $model = $model->refreshed();
        $modelPhaseDates = $this->getPhaseDates($model);

        $this->assertNotNull($model->registrationFrom);
        $this->assertNotNull($model->registrationTo);
        $this->assertNotNull($model->evaluationMethodConfiguration->evaluationFrom);
        $this->assertNotNull($model->evaluationMethodConfiguration->evaluationTo);
        $this->assertNotNull($model->lastPhase->publishTimestamp);
        $this->assertTrue($model->autoPublish);

        $generated = $this->generateOpportunity($model, $owner->profile->id)->refreshed();

        $this->assertCount(count($model->allPhases), $generated->allPhases);
        foreach ($generated->allPhases as $phase) {
            $this->assertNull($phase->registrationFrom);
            $this->assertNull($phase->registrationTo);
            $this->assertNull($phase->publishTimestamp);
            $this->assertFalse($phase->autoPublish);

            if ($configuration = $phase->evaluationMethodConfiguration) {
                $this->assertNull($configuration->evaluationFrom);
                $this->assertNull($configuration->evaluationTo);
            }
        }

        $this->assertSame($modelPhaseDates, $this->getPhaseDates($model->refreshed()));
    }

    function testGeneratedOpportunityKeepsEvaluationConfigurationOnItsPhase(): void
    {
        $owner = $this->userDirector->createUser();
        $model = $this->markAsModel($this->createOpportunityWithEvaluationPhase($owner));

        $this->assertNull($this->configurationOf($model));

        $generated = $this->generateOpportunity($model, $owner->profile->id)->refreshed();
        $phasesWithConfiguration = $this->phasesWithConfiguration($generated);

        $this->assertNull($this->configurationOf($generated));
        $this->assertCount(1, $phasesWithConfiguration);
        $this->assertSame('simple', $this->configurationOf($phasesWithConfiguration[0])->type->id);
    }

    function testGeneratedOpportunityFromModelWithRootAndPhaseConfigurations(): void
    {
        $owner = $this->userDirector->createUser();
        $model = $this->markAsModel($this->createOpportunityWithRootAndPhaseConfigurations($owner));

        $this->assertNotNull($this->configurationOf($model));
        $this->assertCount(1, $this->phasesWithConfiguration($model));

        $generated = $this->generateOpportunity($model, $owner->profile->id)->refreshed();
        $phasesWithConfiguration = $this->phasesWithConfiguration($generated);

        $this->assertNotNull($this->configurationOf($generated));
        $this->assertCount(1, $phasesWithConfiguration);
    }

    function testGeneratedOpportunityPreservesPhaseIdentityMetadata(): void
    {
        $owner = $this->userDirector->createUser();
        $model = $this->markAsModel($this->createOpportunityWithRootAndPhaseConfigurations($owner));
        $modelPhase = $this->phasesWithConfiguration($model)[0];

        $this->assertSame('1', $this->phaseMetadataRows($modelPhase)['isOpportunityPhase'] ?? null);
        $this->assertSame('0', $this->phaseMetadataRows($modelPhase)['isDataCollection'] ?? null);

        $generated = $this->generateOpportunity($model, $owner->profile->id)->refreshed();
        $generatedPhase = $this->findPhaseByName($generated, $modelPhase->name);

        $this->assertNotNull($generatedPhase);
        $this->assertSame(
            $this->phaseMetadataRows($modelPhase),
            $this->phaseMetadataRows($generatedPhase)
        );
    }

    function testModelGeneratedFromOpportunityKeepsConfigurationOnItsPhase(): void
    {
        $owner = $this->userDirector->createUser();
        $source = $this->createOpportunityWithEvaluationPhase($owner);
        $sourcePhase = $this->phasesWithConfiguration($source)[0];

        $model = $this->generateModelFromOpportunity($source, 'Modelo fiel ' . uniqid('', true));
        $modelPhasesWithConfiguration = $this->phasesWithConfiguration($model);

        $this->assertNull($this->configurationOf($model));
        $this->assertCount(1, $modelPhasesWithConfiguration);
        $this->assertSame(
            $this->phaseMetadataRows($sourcePhase),
            $this->phaseMetadataRows($modelPhasesWithConfiguration[0])
        );
    }

    function testSecondGenerationCopyPreservesPhaseIdentityMetadata(): void
    {
        $owner = $this->userDirector->createUser();
        $source = $this->createOpportunityWithRootAndPhaseConfigurations($owner);
        $sourcePhase = $this->phasesWithConfiguration($source)[0];

        $model = $this->generateModelFromOpportunity($source, 'Modelo em cadeia ' . uniqid('', true));
        $generated = $this->generateOpportunity($model, $owner->profile->id)->refreshed();
        $generatedPhase = $this->findPhaseByName($generated, $sourcePhase->name);

        $this->assertNotNull($generatedPhase);
        $this->assertSame(
            $this->phaseMetadataRows($sourcePhase),
            $this->phaseMetadataRows($generatedPhase)
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
        } catch (EntityNotFoundException) {
            // a serialização da resposta falha com proxies deste fixture; a persistência já terminou
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
        } catch (EntityNotFoundException) {
            // a serialização da resposta falha com proxies deste fixture; a persistência já terminou
        }

        return $app->repo('Opportunity')->findOneBy(['name' => $name])->refreshed();
    }

    private function createOpportunityWithEvaluationPhase($owner): Opportunity
    {
        $this->login($owner);
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

    private function createOpportunityWithRootAndPhaseConfigurations($owner): Opportunity
    {
        $this->login($owner);
        $builder = $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $owner->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save();

        $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->fillRequiredProperties()
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->done();

        $builder->addEvaluationPhase(EvaluationMethods::simple)
            ->fillRequiredProperties()
            ->setEvaluationPeriod(new ConcurrentEndingAfter)
            ->save()
            ->done();

        return $builder->refresh()->getInstance();
    }

    private function markAsModel(Opportunity $opportunity): Opportunity
    {
        $opportunity->setMetadata('isModel', 1);
        $opportunity->setMetadata('isModelPublic', 1);
        $opportunity->save(true);

        return $opportunity->refreshed();
    }

    /** Configuração de avaliação pelo lado dono da associação — o inverso não é confiável na transação do teste. */
    private function configurationOf(Opportunity $opportunity): ?EvaluationMethodConfiguration
    {
        return $this->app->repo('EvaluationMethodConfiguration')->findOneBy(['opportunity' => $opportunity]);
    }

    /** Fases filhas que carregam configuração de avaliação, por consulta direta — sem passar por allPhases. @return Opportunity[] */
    private function phasesWithConfiguration(Opportunity $opportunity): array
    {
        $children = $this->app->repo('Opportunity')->findBy(['parent' => $opportunity], ['id' => 'ASC']);

        return array_values(array_filter(
            $children,
            fn(Opportunity $phase) => $this->configurationOf($phase)
        ));
    }

    /** Linhas reais de opportunity_meta da fase, como chave => valor ordenado; booleanos normalizados como o banco grava. */
    private function phaseMetadataRows(Opportunity $phase): array
    {
        $rows = [];
        foreach ($this->app->repo('OpportunityMeta')->findBy(['owner' => $phase]) as $meta) {
            $rows[$meta->key] = is_bool($meta->value) ? ($meta->value ? '1' : '0') : $meta->value;
        }
        ksort($rows);

        return $rows;
    }

    private function findPhaseByName(Opportunity $opportunity, string $name): ?Opportunity
    {
        return $this->app->repo('Opportunity')->findOneBy(['parent' => $opportunity, 'name' => $name]);
    }

    private function countDataCollectionPhases(Opportunity $opportunity): int
    {
        return count(array_filter(
            $opportunity->allPhases,
            fn(Opportunity $phase) => $phase->isDataCollection && !$phase->isLastPhase
        ));
    }

    private function getPhaseDates(Opportunity $opportunity): array
    {
        return array_map(
            static fn(Opportunity $phase) => [
                'registrationFrom' => $phase->registrationFrom?->format('Y-m-d H:i:s'),
                'registrationTo' => $phase->registrationTo?->format('Y-m-d H:i:s'),
                'publishTimestamp' => $phase->publishTimestamp?->format('Y-m-d H:i:s'),
                'autoPublish' => $phase->autoPublish,
                'evaluationFrom' => $phase->evaluationMethodConfiguration?->evaluationFrom?->format('Y-m-d H:i:s'),
                'evaluationTo' => $phase->evaluationMethodConfiguration?->evaluationTo?->format('Y-m-d H:i:s'),
            ],
            $opportunity->allPhases
        );
    }
}
