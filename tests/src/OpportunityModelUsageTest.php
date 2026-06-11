<?php

namespace Test;

use Laminas\Diactoros\Response;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Exceptions\Halt;
use MapasCulturais\Exceptions\PermissionDenied;
use Tests\Abstract\TestCase;
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
}
