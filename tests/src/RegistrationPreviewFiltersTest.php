<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Request as MapasRequest;
use Tests\Abstract\TestCase;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Garante que a oportunidade aninhada na inscrição de prévia contém os
 * filtros usados pelo registration-form para exibir seus seletores.
 */
class RegistrationPreviewFiltersTest extends TestCase
{
    use OpportunityBuilder;
    use RequestFactory;
    use UserDirector;

    public function testPreviewIncludesRegistrationFiltersInNestedOpportunity(): void
    {
        $owner = $this->userDirector->createUser();
        $this->login($owner);

        /** @var Opportunity $opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $owner->profile)
            ->fillRequiredProperties()
            ->save()
            ->setCategories(['Categoria A'])
            ->setProponentTypes(['Pessoa Física', 'Pessoa Jurídica'])
            ->setRanges([[
                'label' => 'Faixa A',
                'limit' => 10,
                'value' => 1000,
            ]])
            ->getInstance();

        $app = App::i();
        $previewId = "{$opportunity->id}-preview";
        $psr = $this->requestFactory->GET('registration', 'preview', [$previewId]);
        $app->request = new MapasRequest(
            $psr,
            'registration',
            'preview',
            ['id' => $previewId]
        );

        $controller = $app->controller('registration');
        $controller->setRequestData(['id' => $previewId]);
        $app->view->controller = $controller;

        /** @var Registration $registration */
        $registration = $controller->requestedEntity;
        $app->view->jsObject['requestedEntity'] = null;
        $app->view->addRequestedEntityToJs(
            Registration::class,
            $registration->id,
            entity: $registration
        );

        $requestedEntity = $app->view->jsObject['requestedEntity'] ?? null;
        $this->assertIsArray($requestedEntity);

        $previewOpportunity = $requestedEntity['opportunity'] ?? null;
        $this->assertIsObject($previewOpportunity);
        $this->assertSame(['Categoria A'], $previewOpportunity->registrationCategories);
        $this->assertSame(
            ['Pessoa Física', 'Pessoa Jurídica'],
            $previewOpportunity->registrationProponentTypes
        );
        $this->assertSame(
            [['label' => 'Faixa A', 'limit' => 10, 'value' => 1000]],
            $previewOpportunity->registrationRanges
        );
    }
}
