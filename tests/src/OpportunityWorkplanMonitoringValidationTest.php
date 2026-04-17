<?php
namespace Tests;

require_once __DIR__ . '/bootstrap.php';

use OpportunityWorkplan\Entities\Delivery;
use OpportunityWorkplan\Entities\Goal;
use OpportunityWorkplan\Entities\Workplan;
use Tests\Traits\OpportunityDirector;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class OpportunityWorkplanMonitoringValidationTest extends \MapasCulturais_TestCase
{
    use UserDirector;
    use OpportunityDirector;
    use RegistrationDirector;

    public function testMonitoringFieldsAreNotRequiredDuringRegistrationPhase()
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

        $this->assertArrayNotHasKey('executedRevenue', $deliveryErrors);
        $this->assertArrayNotHasKey('executedCommunicationChannels', $deliveryErrors);
        $this->assertArrayNotHasKey('executedTeamCompositionGender', $deliveryErrors);
        $this->assertArrayNotHasKey('executedCommunityCoauthorsDetail', $deliveryErrors);
    }

    public function testMonitoringGateFieldsAreNotRequiredDuringRegistrationPhase()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        foreach ([false, true] as $required) {
            $opportunity = $this->createOpportunityWithWorkplan([
                'workplan_monitoringInformCommunityCoauthors' => true,
                'workplan_monitoringRequireHasCommunityCoauthors' => $required,
            ]);

            $registration = $this->createRegistrationWithWorkplan($opportunity, $user, [
                'delivery' => [
                    'executedHasCommunityCoauthors' => null,
                ]
            ]);

            $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $registration->id]);
            $delivery = $workplan->goals[0]->deliveries[0];
            $errors = $this->collectSendValidationErrors($registration);
            $deliveryErrors = $errors['workplanProxy']['deliveries'][$delivery->id] ?? [];

            $this->assertArrayNotHasKey(
                'executedHasCommunityCoauthors',
                $deliveryErrors,
                'Campos de monitoramento não devem ser obrigatórios na fase de inscrição'
            );
        }
    }

    public function testMonitoringExclusiveExecutedFieldsAreNotRequiredDuringRegistrationPhase()
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

        $this->assertArrayNotHasKey('executedMonthInitial', $deliveryErrors);
        $this->assertArrayNotHasKey('executedMonthEnd', $deliveryErrors);
        $this->assertArrayNotHasKey('executedTotalBudget', $deliveryErrors);
        $this->assertArrayNotHasKey('executedCommunicationStrategies', $deliveryErrors);
    }

    public function testMonitoringFieldsAreAbsentFromRegistrationPhaseSendErrors()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $monitoringFields = [
            'availabilityType', 'participantProfile', 'priorityAudience',
            'numberOfParticipants', 'executedRevenue', 'executedNumberOfCities',
            'executedPaidStaffByRole', 'executedTeamCompositionGender',
            'executedCommunicationChannels', 'executedRevenueType',
            'executedTotalBudget', 'executedSegmentDelivery',
            'executedCommunicationStrategies', 'executedCommunityCoauthorsDetail',
            'executedTransInclusionActions', 'executedExpectedAccessibilityMeasures',
            'executedEnvironmentalPracticesDescription', 'executedInnovationTypes',
            'executedDocumentationTypes',
        ];

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
            'workplan_monitoringInformInnovation' => true,
            'workplan_monitoringRequireInnovationTypes' => true,
            'workplan_monitoringInformDocumentationTypes' => true,
            'workplan_monitoringRequireDocumentationTypes' => true,
        ]);

        $registration = $this->createRegistrationWithWorkplan($opportunity, $user);

        $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $registration->id]);
        $delivery = $workplan->goals[0]->deliveries[0];

        $errors = $this->collectSendValidationErrors($registration);
        $deliveryErrors = $errors['workplanProxy']['deliveries'][$delivery->id] ?? [];

        foreach ($monitoringFields as $field) {
            $this->assertArrayNotHasKey(
                $field,
                $deliveryErrors,
                "Campo de monitoramento '{$field}' não deve ser obrigatório na fase de inscrição"
            );
        }
    }

    public function testProjectMonitoringValidationSummaryListsStructuredMonitoringErrors()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        // Flags de monitoramento ficam na fase de inscrição (firstPhase), pois é onde
        // isMetadataRequired e o branch de monitoramento em sendValidationErrors as lêem.
        $firstPhaseOpportunity = $this->createOpportunityWithWorkplan([
            'workplan_monitoringInformTheFormOfAvailability' => true,
            'workplan_monitoringRequireAvailabilityType' => true,
            'workplan_monitoringReportExecutedRevenue' => true,
            'workplan_monitoringRequireExecutedRevenue' => true,
        ]);

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
        $monitoringRegistration->save(true);

        $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $firstPhaseRegistration->id]);
        $delivery = $workplan->goals[0]->deliveries[0];

        $errors = $this->collectSendValidationErrors($monitoringRegistration);

        $this->assertArrayHasKey('workplanProxy', $errors);
        $this->assertNotEmpty($errors['delivery'] ?? []);
        $this->assertTrue(
            $this->arrayContainsSubstring($errors['delivery'], 'Forma de disponibilização'),
            'Resumo do monitoramento não incluiu o label amigável da entrega'
        );
        $this->assertTrue(
            $this->arrayContainsSubstring($errors['delivery'], $delivery->name),
            'Resumo do monitoramento não incluiu o nome da entrega'
        );
        $this->assertArrayHasKey('availabilityType', $errors['workplanProxy']['deliveries'][$delivery->id] ?? []);
        $this->assertArrayHasKey('executedRevenue', $errors['workplanProxy']['deliveries'][$delivery->id] ?? []);
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
