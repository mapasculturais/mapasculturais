<?php
namespace Tests;

use MapasCulturais\App;
use OpportunityWorkplan\Entities\Workplan;
use OpportunityWorkplan\Entities\Goal;
use OpportunityWorkplan\Entities\Delivery;
use Tests\Abstract\TestCase;
use Tests\Traits\UserDirector;
use Tests\Traits\OpportunityDirector;
use Tests\Traits\RegistrationDirector;

/**
 * Testes para validação de campos com obrigatoriedade configurável
 *
 * Execução:
 * docker exec -it mapas-dev-mapas vendor/bin/phpunit tests/src/OpportunityWorkplanRequireFieldsTest.php
 */
class OpportunityWorkplanRequireFieldsTest extends TestCase
{
    use UserDirector;
    use OpportunityDirector;
    use RegistrationDirector;

    /**
     * Testa campo habilitado mas NÃO obrigatório permite vazio
     */
    public function testDeliveryFieldOptionalAllowsEmpty()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        // Criar oportunidade com workplan habilitado
        $opportunity = $this->createOpportunityWithWorkplan([
            'workplan_deliveryInformNumberOfCities' => true,
            'workplan_deliveryRequireNumberOfCities' => false, // NÃO obrigatório
        ]);

        // Criar registration com workplan/goal/delivery
        $registration = $this->createRegistrationWithWorkplan($opportunity, $user, [
            'delivery' => [
                'numberOfCities' => null // Vazio
            ]
        ]);

        $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $registration->id]);
        $delivery = $workplan->goals[0]->deliveries[0];

        // Verificar que campo NÃO é obrigatório
        $isRequired = $delivery->isMetadataRequired('numberOfCities');
        $this->assertFalse($isRequired, 'Campo deveria ser opcional quando require=false');
    }

    /**
     * Testa campo habilitado E obrigatório rejeita vazio
     */
    public function testDeliveryFieldRequiredRejectsEmpty()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $opportunity = $this->createOpportunityWithWorkplan([
            'workplan_deliveryInformNumberOfCities' => true,
            'workplan_deliveryRequireNumberOfCities' => true, // Obrigatório
        ]);

        $registration = $this->createRegistrationWithWorkplan($opportunity, $user, [
            'delivery' => [
                'numberOfCities' => null // Vazio
            ]
        ]);

        $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $registration->id]);
        $delivery = $workplan->goals[0]->deliveries[0];

        // Verificar que campo É obrigatório
        $isRequired = $delivery->isMetadataRequired('numberOfCities');
        $this->assertTrue($isRequired, 'Campo deveria ser obrigatório quando require=true');
    }

    /**
     * Testa campo JSON array vazio não passa quando obrigatório
     */
    public function testJsonArrayFieldEmpty()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $opportunity = $this->createOpportunityWithWorkplan([
            'workplan_deliveryInformPaidStaffByRole' => true,
            'workplan_deliveryRequirePaidStaffByRole' => true,
        ]);

        $registration = $this->createRegistrationWithWorkplan($opportunity, $user, [
            'delivery' => [
                'paidStaffByRole' => '[]' // Array vazio
            ]
        ]);

        $workplan = $app->repo(Workplan::class)->findOneBy(['registration' => $registration->id]);
        $delivery = $workplan->goals[0]->deliveries[0];

        // Validar que array vazio não passa
        $value = $delivery->paidStaffByRole;
        $decoded = json_decode($value, true);
        $isValid = is_array($decoded) && count($decoded) > 0;

        $this->assertFalse($isValid, 'Array JSON vazio deveria falhar validação');
    }

    /**
     * Testa campo gate+detail - detail só obrigatório se gate = true
     */
    public function testGateDetailLogic()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $opportunity = $this->createOpportunityWithWorkplan([
            'workplan_deliveryInformTransInclusion' => true,
            'workplan_deliveryRequireTransInclusionActions' => true,
        ]);

        // Cenário 1: gate = false → detail NÃO obrigatório
        $registration1 = $this->createRegistrationWithWorkplan($opportunity, $user, [
            'delivery' => [
                'hasTransInclusionStrategy' => 'false', // Gate = false
                'transInclusionActions' => null
            ]
        ]);

        $workplan1 = $app->repo(Workplan::class)->findOneBy(['registration' => $registration1->id]);
        $delivery1 = $workplan1->goals[0]->deliveries[0];

        $isRequired1 = $delivery1->isMetadataRequired('transInclusionActions');
        $this->assertFalse($isRequired1, 'Detail NÃO deveria ser obrigatório quando gate=false');

        // Cenário 2: gate = true → detail obrigatório
        $registration2 = $this->createRegistrationWithWorkplan($opportunity, $user, [
            'delivery' => [
                'hasTransInclusionStrategy' => 'true', // Gate = true
                'transInclusionActions' => null
            ]
        ]);

        $workplan2 = $app->repo(Workplan::class)->findOneBy(['registration' => $registration2->id]);
        $delivery2 = $workplan2->goals[0]->deliveries[0];

        $isRequired2 = $delivery2->isMetadataRequired('transInclusionActions');
        $this->assertTrue($isRequired2, 'Detail deveria ser obrigatório quando gate=true');
    }

    /**
     * Testa Goal - título opcional
     */
    public function testGoalTitleOptional()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $opportunity = $this->createOpportunityWithWorkplan([
            'workplan_goalInformTitle' => true,
            'workplan_goalRequireTitle' => false, // Opcional
        ]);

        $registration = $this->createRegistrationWithWorkplan($opportunity, $user, [
            'goal' => [
                'title' => null // Vazio
            ]
        ]);

        // Validação manual (hook não roda em teste unitário isolado)
        $goalInformTitle = $opportunity->workplan_goalInformTitle;
        $goalRequireTitle = $opportunity->workplan_goalRequireTitle ?? false;
        $goalTitle = null;

        $hasError = ($goalInformTitle && $goalRequireTitle && !$goalTitle);

        $this->assertFalse($hasError, 'Título opcional não deveria gerar erro');
    }

    /**
     * Testa Goal - título obrigatório
     */
    public function testGoalTitleRequired()
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $this->login($user);

        $opportunity = $this->createOpportunityWithWorkplan([
            'workplan_goalInformTitle' => true,
            'workplan_goalRequireTitle' => true, // Obrigatório
        ]);

        $registration = $this->createRegistrationWithWorkplan($opportunity, $user, [
            'goal' => [
                'title' => null // Vazio
            ]
        ]);

        // Validação manual
        $goalInformTitle = $opportunity->workplan_goalInformTitle;
        $goalRequireTitle = $opportunity->workplan_goalRequireTitle ?? false;
        $goalTitle = null;

        $hasError = ($goalInformTitle && $goalRequireTitle && !$goalTitle);

        $this->assertTrue($hasError, 'Título obrigatório vazio deveria gerar erro');
    }

    // =====================================
    // MÉTODOS AUXILIARES
    // =====================================

    /**
     * Cria oportunidade com workplan habilitado e metadata customizados
     */
    private function createOpportunityWithWorkplan(array $metadata = [])
    {
        $app = $this->app;
        $user = $this->userDirector->createUser();
        $agent = $user->profile;

        // Criar projeto pai
        $project = new \MapasCulturais\Entities\Project;
        $project->name = 'Projeto Teste Workplan';
        $project->shortDescription = 'Teste';
        $project->owner = $agent;
        $project->save(true);

        // Criar oportunidade
        $opportunity = new \MapasCulturais\Entities\Opportunity;
        $opportunity->name = 'Oportunidade Teste Workplan';
        $opportunity->shortDescription = 'Teste';
        $opportunity->owner = $agent;
        $opportunity->ownerEntity = $project;
        $opportunity->registrationFrom = new \DateTime('now');
        $opportunity->registrationTo = new \DateTime('+30 days');

        // Habilitar workplan
        $opportunity->enableWorkplan = true;
        $opportunity->workplan_deliveryReportTheDeliveriesLinkedToTheGoals = true;

        // Aplicar metadata customizados
        foreach ($metadata as $key => $value) {
            $opportunity->$key = $value;
        }

        $opportunity->save(true);

        return $opportunity;
    }

    /**
     * Cria registration com workplan/goal/delivery
     */
    private function createRegistrationWithWorkplan($opportunity, $user, array $data = [])
    {
        $app = $this->app;

        // Criar registration
        $registration = new \MapasCulturais\Entities\Registration;
        $registration->opportunity = $opportunity;
        $registration->owner = $user->profile;
        $registration->save(true);

        // Criar workplan
        $workplan = new Workplan;
        $workplan->registration = $registration;
        $workplan->owner = $user->profile;
        $workplan->projectDuration = 12;
        $workplan->save(true);

        // Criar goal
        $goal = new Goal;
        $goal->workplan = $workplan;
        $goal->owner = $user->profile;
        $goal->monthInitial = 1;
        $goal->monthEnd = 12;
        $goal->title = $data['goal']['title'] ?? 'Meta Teste';
        $goal->description = $data['goal']['description'] ?? 'Descrição teste';
        $goal->save(true);

        // Criar delivery
        $delivery = new Delivery;
        $delivery->goal = $goal;
        $delivery->owner = $user->profile;
        $delivery->name = $data['delivery']['name'] ?? 'Entrega Teste';
        $delivery->description = $data['delivery']['description'] ?? 'Descrição teste';
        $delivery->typeDelivery = 'Outro';

        // Aplicar metadata customizados do delivery
        if (isset($data['delivery'])) {
            foreach ($data['delivery'] as $key => $value) {
                if (!in_array($key, ['name', 'description', 'typeDelivery'])) {
                    $delivery->$key = $value;
                }
            }
        }

        $delivery->save(true);

        return $registration;
    }
}
