<?php
namespace Tests;

require_once __DIR__ . '/bootstrap.php';

use OpportunityWorkplan\Entities\Delivery;
use OpportunityWorkplan\Entities\Goal;
use OpportunityWorkplan\Entities\Workplan;
use OpportunityWorkplan\Module as OpportunityWorkplanModule;
use Tests\Traits\OpportunityDirector;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class OpportunityWorkplanMonitoringValidationTest extends \MapasCulturais_TestCase
{
    use UserDirector;
    use OpportunityDirector;
    use RegistrationDirector;

    public function testMonitoringValidationAddsStructuredErrorsPerDeliveryField()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $opportunity = $this->createOpportunityWithWorkplan([
            'workplan_monitoringReportExecutedRevenue' => true,
            'workplan_monitoringRequireExecutedRevenue' => true,
            'workplan_monitoringInformCommunicationChannels' => true,
            'workplan_monitoringRequireCommunicationChannels' => true,
            'workplan_monitoringInformTeamComposition' => true,
            'workplan_monitoringRequireTeamCompositionGender' => true,
            'workplan_monitoringInformCommunityCoauthors' => true,
            'workplan_monitoringRequireCommunityCoauthorsDetail' => true,
        ]);

        $registration = $this->createRegistrationWithWorkplan($opportunity, $user, [
            'delivery' => [
                'executedRevenue' => null,
                'executedCommunicationChannels' => '[]',
                'executedTeamCompositionGender' => '{}',
                'executedHasCommunityCoauthors' => 'true',
                'executedCommunityCoauthorsDetail' => null,
            ]
        ]);

        $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $registration->id]);
        $delivery = $workplan->goals[0]->deliveries[0];

        $errors = $this->collectSendValidationErrors($registration);
        $deliveryErrors = $errors['workplanProxy']['deliveries'][$delivery->id] ?? [];

        $this->assertArrayHasKey('executedRevenue', $deliveryErrors);
        $this->assertArrayHasKey('executedCommunicationChannels', $deliveryErrors);
        $this->assertArrayHasKey('executedTeamCompositionGender', $deliveryErrors);
        $this->assertArrayHasKey('executedCommunityCoauthorsDetail', $deliveryErrors);

        $this->assertNotEmpty($errors['delivery'] ?? []);
    }

    public function testMonitoringGateFieldsOnlyValidateWhenConfiguredAsRequired()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $optionalOpportunity = $this->createOpportunityWithWorkplan([
            'workplan_monitoringInformCommunityCoauthors' => true,
            'workplan_monitoringRequireHasCommunityCoauthors' => false,
        ]);

        $optionalRegistration = $this->createRegistrationWithWorkplan($optionalOpportunity, $user, [
            'delivery' => [
                'executedHasCommunityCoauthors' => null,
            ]
        ]);

        $optionalWorkplan = $app->repo(Workplan::class)->findOneBy(['registration' => $optionalRegistration->id]);
        $optionalDelivery = $optionalWorkplan->goals[0]->deliveries[0];
        $optionalErrors = $this->collectSendValidationErrors($optionalRegistration);
        $optionalDeliveryErrors = $optionalErrors['workplanProxy']['deliveries'][$optionalDelivery->id] ?? [];

        $this->assertArrayNotHasKey('executedHasCommunityCoauthors', $optionalDeliveryErrors);

        $requiredOpportunity = $this->createOpportunityWithWorkplan([
            'workplan_monitoringInformCommunityCoauthors' => true,
            'workplan_monitoringRequireHasCommunityCoauthors' => true,
        ]);

        $requiredRegistration = $this->createRegistrationWithWorkplan($requiredOpportunity, $user, [
            'delivery' => [
                'executedHasCommunityCoauthors' => null,
            ]
        ]);

        $requiredWorkplan = $app->repo(Workplan::class)->findOneBy(['registration' => $requiredRegistration->id]);
        $requiredDelivery = $requiredWorkplan->goals[0]->deliveries[0];
        $requiredErrors = $this->collectSendValidationErrors($requiredRegistration);
        $requiredDeliveryErrors = $requiredErrors['workplanProxy']['deliveries'][$requiredDelivery->id] ?? [];

        $this->assertArrayHasKey('executedHasCommunityCoauthors', $requiredDeliveryErrors);
    }

    public function testMonitoringValidationAddsStructuredErrorsForExclusiveExecutedFields()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $opportunity = $this->createOpportunityWithWorkplan([
            'workplan_monitoringInformExecutedDeliveryPeriod' => true,
            'workplan_monitoringRequireExecutedDeliveryPeriod' => true,
            'workplan_monitoringInformExecutedTotalBudget' => true,
            'workplan_monitoringRequireExecutedTotalBudget' => true,
            'workplan_monitoringInformExecutedCommunicationStrategies' => true,
            'workplan_monitoringRequireExecutedCommunicationStrategies' => true,
        ]);

        $registration = $this->createRegistrationWithWorkplan($opportunity, $user, [
            'delivery' => [
                'executedMonthInitial' => null,
                'executedMonthEnd' => null,
                'executedTotalBudget' => null,
                'executedCommunicationStrategies' => null,
            ]
        ]);

        $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $registration->id]);
        $delivery = $workplan->goals[0]->deliveries[0];

        $errors = $this->collectSendValidationErrors($registration);
        $deliveryErrors = $errors['workplanProxy']['deliveries'][$delivery->id] ?? [];

        $this->assertArrayHasKey('executedMonthInitial', $deliveryErrors);
        $this->assertArrayHasKey('executedMonthEnd', $deliveryErrors);
        $this->assertArrayHasKey('executedTotalBudget', $deliveryErrors);
        $this->assertArrayHasKey('executedCommunicationStrategies', $deliveryErrors);
    }

    public function testMonitoringSummaryListsEveryStructuredRequiredFieldWithFriendlyLabel()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $opportunity = $this->createOpportunityWithWorkplan([
            'workplan_monitoringInformTheFormOfAvailability' => true,
            'workplan_monitoringRequireAvailabilityType' => true,
            'workplan_monitoringProvideTheProfileOfParticipants' => true,
            'workplan_monitoringRequireParticipantProfile' => true,
            'workplan_monitoringInformThePriorityAudience' => true,
            'workplan_monitoringRequirePriorityAudience' => true,
            'workplan_monitoringInformNumberOfParticipants' => true,
            'workplan_monitoringRequireNumberOfParticipants' => true,
            'workplan_monitoringReportExecutedRevenue' => true,
            'workplan_monitoringRequireExecutedRevenue' => true,
            'workplan_monitoringInformNumberOfCities' => true,
            'workplan_monitoringRequireNumberOfCities' => true,
            'workplan_monitoringInformPaidStaffByRole' => true,
            'workplan_monitoringRequirePaidStaffByRole' => true,
            'workplan_monitoringInformTeamComposition' => true,
            'workplan_monitoringRequireTeamCompositionGender' => true,
            'workplan_monitoringInformCommunicationChannels' => true,
            'workplan_monitoringRequireCommunicationChannels' => true,
            'workplan_monitoringInformRevenueType' => true,
            'workplan_monitoringRequireRevenueType' => true,
            'workplan_monitoringInformExecutedTotalBudget' => true,
            'workplan_monitoringRequireExecutedTotalBudget' => true,
            'workplan_monitoringInformSegmentDelivery' => true,
            'workplan_monitoringRequireSegmentDelivery' => true,
            'workplan_monitoringInformExecutedCommunicationStrategies' => true,
            'workplan_monitoringRequireExecutedCommunicationStrategies' => true,
            'workplan_monitoringInformCommunityCoauthors' => true,
            'workplan_monitoringRequireCommunityCoauthorsDetail' => true,
            'workplan_monitoringInformTransInclusion' => true,
            'workplan_monitoringRequireTransInclusionActions' => true,
            'workplan_monitoringInformAccessibilityPlan' => true,
            'workplan_monitoringRequireExpectedAccessibilityMeasures' => true,
            'workplan_monitoringInformEnvironmentalPractices' => true,
            'workplan_monitoringRequireEnvironmentalPracticesDescription' => true,
            'workplan_monitoringInformPressStrategy' => true,
            'workplan_monitoringRequireHasPressStrategy' => true,
            'workplan_monitoringInformInnovation' => true,
            'workplan_monitoringRequireInnovationTypes' => true,
            'workplan_monitoringInformDocumentationTypes' => true,
            'workplan_monitoringRequireDocumentationTypes' => true,
        ]);

        $registration = $this->createRegistrationWithWorkplan($opportunity, $user, [
            'delivery' => [
                'availabilityType' => null,
                'participantProfile' => null,
                'priorityAudience' => '[]',
                'numberOfParticipants' => null,
                'executedRevenue' => null,
                'executedNumberOfCities' => null,
                'executedPaidStaffByRole' => '[]',
                'executedTeamCompositionGender' => '{}',
                'executedCommunicationChannels' => '[]',
                'executedRevenueType' => '[]',
                'executedTotalBudget' => null,
                'executedSegmentDelivery' => null,
                'executedCommunicationStrategies' => null,
                'executedHasCommunityCoauthors' => 'true',
                'executedCommunityCoauthorsDetail' => null,
                'executedHasTransInclusionStrategy' => 'true',
                'executedTransInclusionActions' => null,
                'executedHasAccessibilityPlan' => 'true',
                'executedExpectedAccessibilityMeasures' => '[]',
                'executedHasEnvironmentalPractices' => 'true',
                'executedEnvironmentalPracticesDescription' => null,
                'executedHasPressStrategy' => null,
                'executedHasInnovationAction' => 'true',
                'executedInnovationTypes' => '[]',
                'executedDocumentationTypes' => '[]',
            ]
        ]);

        $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $registration->id]);
        $delivery = $workplan->goals[0]->deliveries[0];

        $errors = $this->collectSendValidationErrors($registration);
        $deliveryErrors = $errors['workplanProxy']['deliveries'][$delivery->id] ?? [];
        $summaryMessages = $errors['delivery'] ?? [];

        $this->assertNotEmpty($deliveryErrors);
        $this->assertNotEmpty($summaryMessages);

        foreach (array_keys($deliveryErrors) as $field) {
            $label = OpportunityWorkplanModule::getFieldLabel($field);

            $this->assertNotSame($field, $label, "Campo de monitoramento sem label amigável: {$field}");
            $this->assertTrue(
                $this->arrayContainsSubstring($summaryMessages, $label),
                "Resumo de validação não inclui o label '{$label}' para o campo '{$field}'. Mensagens: " . print_r($summaryMessages, true)
            );
            $this->assertTrue(
                $this->arrayContainsSubstring($summaryMessages, $delivery->name),
                "Resumo de validação não informa a entrega relacionada. Mensagens: " . print_r($summaryMessages, true)
            );
        }
    }

    public function testProjectMonitoringValidationSummaryListsStructuredMonitoringErrors()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $firstPhaseOpportunity = $this->createOpportunityWithWorkplan();

        $firstPhaseRegistration = $this->createRegistrationWithWorkplan($firstPhaseOpportunity, $user);

        $monitoringPhaseOpportunity = new \MapasCulturais\Entities\Opportunity;
        $monitoringPhaseOpportunity->name = 'Fase de Monitoramento';
        $monitoringPhaseOpportunity->shortDescription = 'Teste';
        $monitoringPhaseOpportunity->owner = $firstPhaseOpportunity->owner;
        $monitoringPhaseOpportunity->ownerEntity = $firstPhaseOpportunity->ownerEntity;
        $monitoringPhaseOpportunity->parent = $firstPhaseOpportunity;
        $monitoringPhaseOpportunity->registrationFrom = new \DateTime('now');
        $monitoringPhaseOpportunity->registrationTo = new \DateTime('+30 days');
        $monitoringPhaseOpportunity->isReportingPhase = true;
        $monitoringPhaseOpportunity->includesWorkPlan = true;
        $monitoringPhaseOpportunity->save(true);

        $monitoringRegistration = new \MapasCulturais\Entities\Registration;
        $monitoringRegistration->opportunity = $monitoringPhaseOpportunity;
        $monitoringRegistration->owner = $user->profile;
        $monitoringRegistration->firstPhase = $firstPhaseRegistration;
        $monitoringRegistration->workplanProxy = (object) [
            'goals' => [],
            'deliveries' => [],
        ];
        $monitoringRegistration->save(true);

        $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $firstPhaseRegistration->id]);
        $goal = $workplan->goals[0];
        $delivery = $goal->deliveries[0];

        $monitoringRegistration->workplanProxy = (object) [
            'goals' => [
                $goal->id => (object) [
                    'status' => null,
                    'executionDetail' => null,
                ],
            ],
            'deliveries' => [
                $delivery->id => (object) [
                    'status' => null,
                    'availabilityType' => null,
                    'executedRevenue' => null,
                ],
            ],
        ];

        $monitoringPhaseOpportunity->workplan_monitoringInformTheFormOfAvailability = true;
        $monitoringPhaseOpportunity->workplan_monitoringRequireAvailabilityType = true;
        $monitoringPhaseOpportunity->workplan_monitoringReportExecutedRevenue = true;
        $monitoringPhaseOpportunity->workplan_monitoringRequireExecutedRevenue = true;

        $monitoringPhaseOpportunity->save(true);
        $monitoringRegistration->save(true);

        $errors = $this->collectValidationErrors($monitoringRegistration);

        $this->assertArrayHasKey('workplanProxy', $errors);
        $this->assertNotEmpty($errors['goal'] ?? []);
        $this->assertNotEmpty($errors['delivery'] ?? []);
        $this->assertTrue(
            $this->arrayContainsSubstring($errors['goal'], 'Detalhamento da execução da meta'),
            'Resumo do monitoramento não incluiu o label amigável da meta'
        );
        $this->assertTrue(
            $this->arrayContainsSubstring($errors['delivery'], 'Forma de disponibilização'),
            'Resumo do monitoramento não incluiu o label amigável da entrega'
        );
        $this->assertTrue(
            $this->arrayContainsSubstring($errors['delivery'], $delivery->name),
            'Resumo do monitoramento não incluiu o nome da entrega'
        );
    }

    private function createOpportunityWithWorkplan(array $metadata = [])
    {
        $user = $this->userDirector->createUser();
        $agent = $user->profile;

        $project = new \MapasCulturais\Entities\Project;
        $project->name = 'Projeto Teste Monitoramento';
        $project->shortDescription = 'Teste';
        $project->owner = $agent;
        $project->save(true);

        $opportunity = new \MapasCulturais\Entities\Opportunity;
        $opportunity->name = 'Oportunidade Teste Monitoramento';
        $opportunity->shortDescription = 'Teste';
        $opportunity->owner = $agent;
        $opportunity->ownerEntity = $project;
        $opportunity->registrationFrom = new \DateTime('now');
        $opportunity->registrationTo = new \DateTime('+30 days');
        $opportunity->enableWorkplan = true;
        $opportunity->workplan_deliveryReportTheDeliveriesLinkedToTheGoals = true;

        foreach ($metadata as $key => $value) {
            $opportunity->$key = $value;
        }

        $opportunity->save(true);

        return $opportunity;
    }

    private function createRegistrationWithWorkplan($opportunity, $user, array $data = [])
    {
        $registration = new \MapasCulturais\Entities\Registration;
        $registration->opportunity = $opportunity;
        $registration->owner = $user->profile;
        $registration->save(true);

        $workplan = new Workplan;
        $workplan->registration = $registration;
        $workplan->owner = $user->profile;
        $workplan->projectDuration = 12;
        $workplan->save(true);

        $goal = new Goal;
        $goal->workplan = $workplan;
        $goal->owner = $user->profile;
        $goal->monthInitial = 1;
        $goal->monthEnd = 12;
        $goal->title = $data['goal']['title'] ?? 'Meta Teste';
        $goal->description = $data['goal']['description'] ?? 'Descrição teste';
        $goal->save(true);

        $delivery = new Delivery;
        $delivery->goal = $goal;
        $delivery->owner = $user->profile;
        $delivery->name = $data['delivery']['name'] ?? 'Entrega Teste';
        $delivery->description = $data['delivery']['description'] ?? 'Descrição teste';
        $delivery->typeDelivery = 'Outro';

        if (isset($data['delivery'])) {
            foreach ($data['delivery'] as $key => $value) {
                if (!in_array($key, ['name', 'description', 'typeDelivery'], true)) {
                    $delivery->$key = $value;
                }
            }
        }

        $delivery->save(true);

        return $registration;
    }

    private function collectSendValidationErrors($registration): array
    {
        $errors = [];
        $this->app->applyHookBoundTo($registration, 'entity(Registration).sendValidationErrors', [&$errors]);

        return $errors;
    }

    private function collectValidationErrors($registration): array
    {
        $errors = [];
        $this->app->applyHookBoundTo($registration, 'entity(Registration).validationErrors', [&$errors]);

        return $errors;
    }

    private function arrayContainsSubstring(array $messages, string $needle): bool
    {
        foreach ($messages as $message) {
            if (is_string($message) && str_contains($message, $needle)) {
                return true;
            }
        }

        return false;
    }
}
