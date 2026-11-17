<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\User;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationBuilder;
use Tests\Traits\UserDirector;

/**
 * Quem controla a oportunidade lista todas as inscrições dela sem depender da fila de pcache.
 */
class OpportunityControllerRegistrationsVisibilityTest extends TestCase
{
    use UserDirector,
        OpportunityBuilder,
        RegistrationBuilder;

    /** @return array{0: Opportunity, 1: User} [oportunidade, dono (usuário comum)] */
    private function createOpportunity(): array
    {
        $app = App::i();
        $owner = $this->userDirector->createUser();
        $this->login($owner);

        $app->disableAccessControl();
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $owner->profile, owner_entity: $owner->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance()
            ->refreshed();
        $app->enableAccessControl();

        return [$opportunity, $owner];
    }

    private function addController(Opportunity $opportunity): User
    {
        $app = App::i();
        $controller = $this->userDirector->createUser();

        $app->disableAccessControl();
        $opportunity->createAgentRelation(
            $controller->profile,
            Agent::AGENT_RELATION_ADMIN_GROUP,
            has_control: true,
            save: true,
            flush: true
        );
        $app->enableAccessControl();

        return $controller;
    }

    private function createDraftRegistrationAsApplicant(Opportunity $opportunity): Registration
    {
        $applicant = $this->userDirector->createRegistrationOwnerUser();
        $this->login($applicant);

        return $this->registrationBuilder
            ->reset($opportunity->refreshed(), $applicant->profile)
            ->fillRequiredProperties()
            ->save()
            ->getInstance();
    }

    /** Ids das inscrições que o usuário logado lista pela mesma consulta da tabela e da planilha. */
    private function listedRegistrationIds(Opportunity $opportunity): array
    {
        $app = App::i();
        $result = $app->controller('opportunity')->apiFindRegistrations(
            $opportunity->refreshed(),
            ['@select' => 'id', 'status' => 'GTE(0)']
        );

        $ids = array_column($result->registrations, 'id');
        sort($ids);

        return $ids;
    }

    function testControllersListNewRegistrationsWithoutWaitingForPermissionCacheQueue()
    {
        [$opportunity, $owner] = $this->createOpportunity();
        $controller = $this->addController($opportunity);
        $this->processPCache();

        $registration = $this->createDraftRegistrationAsApplicant($opportunity);

        $this->login($owner);
        $this->assertSame([$registration->id], $this->listedRegistrationIds($opportunity), 'Dono da oportunidade deve listar a inscrição nova sem esperar a fila de pcache.');

        $this->login($controller);
        $this->assertSame([$registration->id], $this->listedRegistrationIds($opportunity), 'Administrador com controle deve listar a inscrição nova sem esperar a fila de pcache.');
    }

    function testControllerAddedLaterListsExistingRegistrations()
    {
        [$opportunity] = $this->createOpportunity();

        $registration_1 = $this->createDraftRegistrationAsApplicant($opportunity);
        $registration_2 = $this->createDraftRegistrationAsApplicant($opportunity);
        $expected = [$registration_1->id, $registration_2->id];
        sort($expected);

        $controller = $this->addController($opportunity);

        $this->login($controller);
        $this->assertSame($expected, $this->listedRegistrationIds($opportunity), 'Administrador incluído depois deve listar as inscrições já existentes sem esperar a fila de pcache.');
    }

    /** Ids das inscrições em que o usuário tem a linha "view" no pcache. */
    private function viewCacheRegistrationIds(User $user, array $registration_ids): array
    {
        $ids = App::i()->em->getConnection()->fetchFirstColumn(
            "SELECT object_id FROM pcache
              WHERE object_type = :type AND action = 'view' AND user_id = :user AND object_id IN (:ids)",
            ['type' => Registration::getClassName(), 'user' => $user->id, 'ids' => $registration_ids],
            ['ids' => \Doctrine\DBAL\ArrayParameterType::INTEGER]
        );
        sort($ids);

        return $ids;
    }

    function testListingGrantsTheSameViewCacheAsFullRecreation()
    {
        $app = App::i();
        [$opportunity] = $this->createOpportunity();

        $registration_1 = $this->createDraftRegistrationAsApplicant($opportunity);
        $registration_2 = $this->createDraftRegistrationAsApplicant($opportunity);
        $registration_ids = [$registration_1->id, $registration_2->id];
        sort($registration_ids);

        $controller = $this->addController($opportunity);

        $this->login($controller);
        $this->listedRegistrationIds($opportunity);
        $granted_by_listing = $this->viewCacheRegistrationIds($controller, $registration_ids);
        $this->assertSame($registration_ids, $granted_by_listing, 'A listagem deve gravar a linha "view" do administrador em todas as inscrições.');

        // Recriação completa sem usuário logado.
        $this->logout();
        $app->reset();
        $registration_1->refreshed()->createPermissionsCacheForUsers();
        $registration_2->refreshed()->createPermissionsCacheForUsers();

        $this->assertSame($granted_by_listing, $this->viewCacheRegistrationIds($controller, $registration_ids), 'A recriação completa deve gravar para o administrador as mesmas linhas "view" que a listagem.');
    }

    function testOtherUsersKeepTheirOwnVisibility()
    {
        [$opportunity] = $this->createOpportunity();

        $registration_1 = $this->createDraftRegistrationAsApplicant($opportunity);
        $this->createDraftRegistrationAsApplicant($opportunity);

        $this->login($registration_1->owner->user);
        $this->assertSame([$registration_1->id], $this->listedRegistrationIds($opportunity), 'Proponente deve listar só a própria inscrição.');

        $this->login($this->userDirector->createUser());
        $this->assertSame([], $this->listedRegistrationIds($opportunity), 'Usuário sem relação com a oportunidade não deve listar inscrições.');

        $this->login($this->userDirector->createUser('admin'));
        $this->assertCount(2, $this->listedRegistrationIds($opportunity), 'Admin deve continuar listando todas as inscrições.');
    }
}
