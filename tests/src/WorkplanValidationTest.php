<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use OpportunityWorkplan\Services\WorkplanService;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class WorkplanValidationTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    private function createOpportunityWithWorkplanEnabled(): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity_builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save();

        $opportunity = $opportunity_builder->getInstance();

        $opportunity->enableWorkplan = true;
        $opportunity->workplan_deliveryReportTheDeliveriesLinkedToTheGoals = true;
        $opportunity->save(true);

        return $opportunity;
    }

    public function testRequireWorkplanWhenEnabled(): void
    {
        $opportunity = $this->createOpportunityWithWorkplanEnabled();

        /** @var Registration $registration */
        $registration = $this->registrationDirector->createSentRegistrations($opportunity, 1)[0];

        $errors = $registration->getSendValidationErrors();

        $this->assertArrayHasKey('workplan', $errors, 'Certificando que o plano de metas é obrigatório quando enableWorkplan está ativo');

        $this->assertArrayHasKey('projectDuration', $errors, 'Certificando que a duração do projeto no plano de metas é obrigatória');
        $this->assertArrayHasKey('culturalArtisticSegment', $errors, 'Certificando que o segmento artístico-cultural no plano de metas é obrigatório');
    }

    public function testNoWorkplanErrorsWhenValid(): void
    {
        $opportunity = $this->createOpportunityWithWorkplanEnabled();

        /** @var Registration $registration */
        $registration = $this->registrationDirector
            ->createSentRegistrations($opportunity, 1)[0];

        $workplan_service = new WorkplanService();

        $data = [
            'workplan' => [
                'projectDuration' => 12,
                'culturalArtisticSegment' => 'Música',
                'goals' => [
                    [
                        'monthInitial' => 1,
                        'monthEnd' => 3,
                        'title' => 'Meta 1',
                        'description' => 'Descrição da meta 1',
                        'culturalMakingStage' => null,
                        'amount' => 1,
                        'deliveries' => [
                            [
                                'name' => 'Entrega 1',
                                'description' => 'Descrição da entrega 1',
                                'typeDelivery' => 'Show realizado',
                                'segmentDelivery' => 'Música',
                                'expectedNumberPeople' => 100,
                                'generaterRevenue' => 'false',
                                'renevueQtd' => null,
                                'unitValueForecast' => null,
                                'totalValueForecast' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $workplan_service->save($registration, null, $data);

        $errors = $registration->getSendValidationErrors();

        $this->assertArrayNotHasKey('workplan', $errors, 'Certificando que não há erro de plano de metas quando o plano de metas está corretamente preenchido');
        $this->assertArrayNotHasKey('projectDuration', $errors, 'Certificando que não há erro de duração do projeto quando o campo está preenchido');
        $this->assertArrayNotHasKey('culturalArtisticSegment', $errors, 'Certificando que não há erro de segmento artístico-cultural quando o campo está preenchido');
        $this->assertArrayNotHasKey('delivery', $errors, 'Certificando que não há erro de entrega quando há pelo menos uma entrega cadastrada para a meta');
    }
}

