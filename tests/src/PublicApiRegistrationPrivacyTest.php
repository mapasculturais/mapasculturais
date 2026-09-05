<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\TestCase;
use Tests\Enums\ProponentTypes;
use Tests\Traits\UserDirector;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Builders\PhasePeriods\Open;

class PublicApiRegistrationPrivacyTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        AgentDirector,
        UserDirector;

    private function createSentRegistration(): Registration
    {
        $app = App::i();

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setProponentTypes()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $app->disableAccessControl();
        $opportunity->publishRegistrations();
        $app->enableAccessControl();

        $registration = $this->registrationDirector->createSentRegistrations(
            $opportunity,
            number_of_registrations: 1,
            proponent_type: ProponentTypes::COLETIVO->value
        )[0];

        return $registration;
    }

    private function findIds(array $params): array
    {
        return array_column((new ApiQuery(Registration::class, $params))->find(), 'id');
    }

    function testEmptyPermissionsDoesNotExposePrivateRegistrationToGuest()
    {
        $app = App::i();
        $registration = $this->createSentRegistration();

        $app->disableAccessControl();
        $reachable = $this->findIds(['@select' => 'id']);
        $app->enableAccessControl();
        $this->assertContains($registration->id, $reachable, 'a inscrição precisa existir e ser consultável');

        $this->logout();
        $this->assertTrue($app->isAccessControlEnabled());

        $this->assertNotContains($registration->id, $this->findIds(['@select' => 'id']),
            'inscrição privada não deve aparecer para anônimo');

        $this->assertNotContains($registration->id, $this->findIds(['@select' => 'id', '@permissions' => '']),
            '@permissions vazio não deve derrubar a proteção da entidade privada');

        $this->assertNotContains($registration->id, $this->findIds(['@select' => 'id', '@permissions' => '0']),
            '@permissions=0 não deve derrubar a proteção da entidade privada');
    }

    function testAgentsDataSnapshotHiddenFromUserWithoutPrivateDataPermission()
    {
        $app = App::i();
        $registration = $this->createSentRegistration();

        $stranger = $this->userDirector->createUser();
        $this->login($stranger);

        $app->disableAccessControl();
        $result = (new ApiQuery(Registration::class, ['@select' => 'id,agentsData']))->find();
        $app->enableAccessControl();

        $row = $this->rowById($result, $registration->id);
        $this->assertNotNull($row, 'a inscrição precisa estar no resultado para testar o snapshot');
        $this->assertSame([], $row['agentsData'],
            'agentsData carrega dado pessoal e deve ser ocultado de quem não pode ver dado privado');
    }

    function testAgentsDataSnapshotVisibleToAdmin()
    {
        $app = App::i();
        $registration = $this->createSentRegistration();

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $app->disableAccessControl();
        $result = (new ApiQuery(Registration::class, ['@select' => 'id,agentsData']))->find();
        $app->enableAccessControl();

        $row = $this->rowById($result, $registration->id);
        $this->assertNotNull($row, 'a inscrição precisa estar no resultado');
        $this->assertNotEmpty($row['agentsData'], 'admin deve continuar vendo o snapshot');
    }

    private function rowById(array $result, int $id): ?array
    {
        foreach ($result as $row) {
            if (($row['id'] ?? null) === $id) {
                return $row;
            }
        }
        return null;
    }
}
