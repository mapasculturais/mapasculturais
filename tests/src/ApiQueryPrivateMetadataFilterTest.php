<?php

namespace Tests;

use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\User;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\AgentDirector;
use Tests\Traits\Faker;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\ProjectDirector;
use Tests\Traits\RegistrationBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\SpaceDirector;
use Tests\Traits\UserDirector;

/**
 * Regressão do vazamento de dados privados via filtro/ordenação da API.
 *
 * Metadado privado (ex.: genero) é escondido na resposta para quem não pode
 * ver dados privados, mas o filtro e a ordenação rodavam sobre o valor no
 * banco. Isso vazava o dado: filtrar por genero=EQ(Feminina) devolvia só
 * agentes femininos mesmo com o campo oculto. A correção limita o filtro por
 * metadado privado às entidades em que o usuário pode ver dados privados
 * (admin: todas; usuário comum: as que ele gerencia; visitante: nenhuma). Nos
 * campos de endereço o filtro vale também nos agentes com localização pública.
 * A ordenação por metadado privado é ignorada para não-admin.
 */
class ApiQueryPrivateMetadataFilterTest extends TestCase
{
    use UserDirector,
        AgentDirector,
        SpaceDirector,
        ProjectDirector,
        Faker,
        OpportunityBuilder,
        RegistrationDirector,
        RegistrationBuilder;

    /** @return array{0: Agent, 1: Agent} agentes [feminina, masculina] */
    private function createAgentsWithGenero($owner = null): array
    {
        $app = App::i();
        $owner = $owner ?: $this->userDirector->createUser();

        $app->disableAccessControl();

        $agent_f = $this->agentDirector->createAgent($owner, 1, fill_requered_properties: true, save: true);
        $agent_f->genero = 'Feminina';
        $agent_f->save(true);

        $agent_m = $this->agentDirector->createAgent($owner, 1, fill_requered_properties: true, save: true);
        $agent_m->genero = 'Masculina';
        $agent_m->save(true);

        $app->enableAccessControl();
        $this->processPCache();

        return [$agent_f, $agent_m];
    }

    private function createAgentWithEstado($owner, string $estado, bool $public_location): Agent
    {
        $app = App::i();

        $app->disableAccessControl();

        $agent = $this->agentDirector->createAgent($owner, 1, fill_requered_properties: true, save: true);
        $agent->En_Estado = $estado;
        $agent->publicLocation = $public_location;
        $agent->save(true);

        $app->enableAccessControl();
        $this->processPCache();

        return $agent;
    }

    private function findIds(array $params, string $class = Agent::class): array
    {
        $query = new ApiQuery($class, ['@select' => 'id'] + $params);

        $ids = array_column($query->find(), 'id');
        sort($ids);

        return $ids;
    }

    /**
     * Cria duas entidades (espaço ou projeto) com emailPrivado preenchido, uma
     * do dono informado e outra de outro usuário.
     *
     * @return array{0: Space|Project, 1: Space|Project} [do dono, de outro usuário]
     */
    private function createEntitiesWithEmailPrivado(string $class, User $owner): array
    {
        $app = App::i();
        $other_owner = $this->userDirector->createUser();

        $app->disableAccessControl();

        $entities = [];
        foreach ([$owner, $other_owner] as $user) {
            $entity = $class === Space::class
                ? $this->spaceDirector->createSpace($user->profile)
                : $this->projectDirector->createProject($user->profile);
            $entity->emailPrivado = 'privado@example.com';
            $entity->save(true);
            $entities[] = $entity;
        }

        $app->enableAccessControl();
        $this->processPCache();

        return $entities;
    }

    private function assertEmailPrivadoFilterRespectsPermissions(string $class)
    {
        $owner = $this->userDirector->createUser();
        [$own, $other] = $this->createEntitiesWithEmailPrivado($class, $owner);
        $params = ['id' => "IN({$own->id},{$other->id})", 'emailPrivado' => 'EQ(privado@example.com)'];

        $this->login($this->userDirector->createUser('admin'));
        $this->assertSame([$own->id, $other->id], $this->findIds($params, $class), 'Admin deve filtrar por emailPrivado em todas as entidades.');

        $this->login($owner);
        $this->assertSame([$own->id], $this->findIds($params, $class), 'Dono deve filtrar por emailPrivado só nas entidades dele.');

        $this->login($this->userDirector->createUser());
        $this->assertSame([], $this->findIds($params, $class), 'Usuário sem permissão não deve filtrar por emailPrivado.');

        $this->logout();
        $this->assertSame([], $this->findIds($params, $class), 'Visitante não deve filtrar por emailPrivado.');
    }

    function testNonAdminCannotFilterAgentsByPrivateMetadata()
    {
        [$agent_f, $agent_m] = $this->createAgentsWithGenero();
        $ids = "IN({$agent_f->id},{$agent_m->id})";

        // Usuário comum, que não é dono dos agentes nem admin.
        $user = $this->userDirector->createUser();
        $this->login($user);

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id',
            'id' => $ids,
            'genero' => 'EQ(Feminina)',
            '@order' => 'id ASC',
        ]);
        $result_ids = array_column($query->find(), 'id');

        $this->assertSame(
            [],
            $result_ids,
            'Filtro por metadado privado sem permissão não deve devolver nenhum registro, para que o resultado não dependa do dado privado.'
        );
    }

    function testNonAdminPrivateMetadataFilterMatchesNothingRegardlessOfValue()
    {
        [$agent_f, $agent_m] = $this->createAgentsWithGenero();
        $ids = "IN({$agent_f->id},{$agent_m->id})";

        $user = $this->userDirector->createUser();
        $this->login($user);

        foreach (['EQ(Feminina)', 'EQ(Masculina)', '!EQ(Feminina)', 'NULL()', '!NULL()'] as $filter) {
            $query = new ApiQuery(Agent::class, [
                '@select' => 'id',
                'id' => $ids,
                'genero' => $filter,
            ]);

            $this->assertSame([], $query->find(), "genero={$filter} não deve devolver registros para não-admin.");
        }
    }

    function testAdminCanFilterAgentsByPrivateMetadata()
    {
        [$agent_f, $agent_m] = $this->createAgentsWithGenero();
        $ids = "IN({$agent_f->id},{$agent_m->id})";

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id',
            'id' => $ids,
            'genero' => 'EQ(Feminina)',
            '@order' => 'id ASC',
        ]);
        $result_ids = array_column($query->find(), 'id');

        $this->assertContains($agent_f->id, $result_ids, 'Admin deve continuar filtrando por metadado privado.');
        $this->assertNotContains($agent_m->id, $result_ids, 'Admin filtrando por genero=EQ(Feminina) não deve receber o agente masculino.');
    }

    function testNonAdminOrderByPrivateMetadataIsIgnoredAndDoesNotLeak()
    {
        [$agent_f, $agent_m] = $this->createAgentsWithGenero();
        $ids = "IN({$agent_f->id},{$agent_m->id})";

        $user = $this->userDirector->createUser();
        $this->login($user);

        // Ordenar por metadado privado é ignorado para não-admin: não lança erro
        // e devolve os dois agentes (a ordem não passa a depender do dado privado).
        $query = new ApiQuery(Agent::class, [
            '@select' => 'id',
            'id' => $ids,
            '@order' => 'genero ASC,id ASC',
        ]);
        $result_ids = array_column($query->find(), 'id');

        $this->assertContains($agent_f->id, $result_ids);
        $this->assertContains($agent_m->id, $result_ids);
    }

    function testOwnerCanFilterOwnAgentsByPrivateMetadata()
    {
        $owner = $this->userDirector->createUser();
        [$own_f, $own_m] = $this->createAgentsWithGenero($owner);
        [$other_f, $other_m] = $this->createAgentsWithGenero();

        $this->login($owner);

        $result_ids = $this->findIds([
            'id' => "IN({$own_f->id},{$own_m->id},{$other_f->id},{$other_m->id})",
            'genero' => 'EQ(Feminina)',
        ]);

        $this->assertContains($own_f->id, $result_ids, 'Dono deve conseguir filtrar os próprios agentes por metadado privado.');
        $this->assertNotContains($own_m->id, $result_ids, 'O filtro deve continuar valendo nos agentes do dono.');
        $this->assertNotContains($other_f->id, $result_ids, 'Agente de outro usuário não pode casar com filtro por metadado privado.');
        $this->assertNotContains($other_m->id, $result_ids);
    }

    function testGuestCannotFilterAgentsByPrivateMetadata()
    {
        [$agent_f, $agent_m] = $this->createAgentsWithGenero();

        $this->logout();

        $result_ids = $this->findIds([
            'id' => "IN({$agent_f->id},{$agent_m->id})",
            'genero' => 'EQ(Feminina)',
        ]);

        $this->assertSame([], $result_ids, 'Visitante não deve conseguir filtrar por metadado privado.');
    }

    function testFilterByAddressMetadataRespectsPublicLocation()
    {
        $owner = $this->userDirector->createUser();
        $public_agent = $this->createAgentWithEstado($owner, 'MG', true);
        $private_agent = $this->createAgentWithEstado($owner, 'MG', false);
        $ids = "IN({$public_agent->id},{$private_agent->id})";

        // Visitante: só agente com localização pública.
        $this->logout();
        $result_ids = $this->findIds(['id' => $ids, 'En_Estado' => 'EQ(MG)']);
        $this->assertSame([$public_agent->id], $result_ids, 'Visitante deve filtrar por estado só nos agentes com localização pública.');

        // Usuário comum sem permissão: só agente com localização pública.
        $this->login($this->userDirector->createUser());
        $result_ids = $this->findIds(['id' => $ids, 'En_Estado' => 'EQ(MG)']);
        $this->assertSame([$public_agent->id], $result_ids, 'Usuário sem permissão deve filtrar por estado só nos agentes com localização pública.');

        // Dono: os dois agentes.
        $this->login($owner);
        $result_ids = $this->findIds(['id' => $ids, 'En_Estado' => 'EQ(MG)', '@order' => 'id ASC']);
        $this->assertSame([$public_agent->id, $private_agent->id], $result_ids, 'Dono deve filtrar por estado também no agente com localização privada.');
    }

    function testUserWithControlCanFilterControlledAgentsByPrivateMetadata()
    {
        $app = App::i();
        $owner = $this->userDirector->createUser();
        [$agent_f, $agent_m] = $this->createAgentsWithGenero($owner);
        $ids = "IN({$agent_f->id},{$agent_m->id})";

        // Usuário que controla diretamente o agente feminino.
        $direct_manager = $this->userDirector->createUser();
        // Usuário que controla o perfil do dono e, por isso, os agentes filhos dele.
        $profile_manager = $this->userDirector->createUser();

        // Cada relação em sua própria etapa, como aconteceria em requisições
        // separadas (o pcache de uma entidade só é refeito uma vez por requisição).
        $app->disableAccessControl();
        $agent_f->createAgentRelation($direct_manager->profile, Agent::AGENT_RELATION_ADMIN_GROUP, has_control: true);
        $app->enableAccessControl();
        $this->processPCache();

        $app->disableAccessControl();
        $owner->profile->createAgentRelation($profile_manager->profile, Agent::AGENT_RELATION_ADMIN_GROUP, has_control: true);
        $app->enableAccessControl();
        $this->processPCache();

        $this->login($direct_manager);
        $this->assertSame(
            [$agent_f->id],
            $this->findIds(['id' => $ids, 'genero' => 'EQ(Feminina)']),
            'Quem controla o agente deve conseguir filtrá-lo por metadado privado.'
        );
        $this->assertSame(
            [],
            $this->findIds(['id' => $ids, 'genero' => 'EQ(Masculina)']),
            'Quem controla só o agente feminino não pode filtrar o agente masculino por metadado privado.'
        );

        $this->login($profile_manager);
        $this->assertSame(
            [$agent_f->id],
            $this->findIds(['id' => $ids, 'genero' => 'EQ(Feminina)']),
            'Quem controla o perfil do dono deve conseguir filtrar os agentes dele por metadado privado.'
        );
        $this->assertSame(
            [$agent_m->id],
            $this->findIds(['id' => $ids, 'genero' => 'EQ(Masculina)'])
        );
    }

    function testSpaceEmailPrivadoFilterRespectsPermissions()
    {
        $this->assertEmailPrivadoFilterRespectsPermissions(Space::class);
    }

    function testProjectEmailPrivadoFilterRespectsPermissions()
    {
        $this->assertEmailPrivadoFilterRespectsPermissions(Project::class);
    }

    function testRegistrationFieldFilterRespectsPermissions()
    {
        $app = App::i();
        $opportunity_owner = $this->userDirector->createUser();
        $this->login($opportunity_owner);

        // Montar avaliadores exige permissões extras; o que importa aqui é o filtro.
        $app->disableAccessControl();
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $opportunity_owner->profile, owner_entity: $opportunity_owner->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->save()
                ->createStep('Etapa 1')
                ->createField('campo_privado', 'text', 'Campo privado')
                ->save()
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão', 1)
                ->save()
                ->addValuers(2, 'Comissão')
                ->done()
            ->getInstance()
            ->refreshed();
        $app->enableAccessControl();

        $field_name = $this->opportunityBuilder->getFieldName('campo_privado', $opportunity);

        // Inscrições criadas com controle de acesso ligado: desligado, o
        // canUser libera tudo e o pcache sai com permissão para todo mundo.
        $registration_1 = $this->registrationDirector->createSentRegistration($opportunity, data: [$field_name => 'valor']);
        $registration_2 = $this->registrationDirector->createSentRegistration($opportunity, data: [$field_name => 'valor']);

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();

        // Recria o pcache das inscrições sem usuário logado, como faz o cron de
        // produção. Recriar logado como dono da oportunidade dá permissão a
        // todos os avaliadores, porque o hook can(Registration.<<view|modify|
        // viewPrivateData>>) do módulo RegistrationFieldTypes usa o usuário
        // logado em vez do usuário verificado (bug à parte desta correção).
        $this->logout();
        $app->reset();
        $registration_1 = $registration_1->refreshed();
        $registration_2 = $registration_2->refreshed();
        $registration_1->createPermissionsCacheForUsers();
        $registration_2->createPermissionsCacheForUsers();

        $ids = "IN({$registration_1->id},{$registration_2->id})";
        $params = ['id' => $ids, $field_name => 'EQ(valor)'];
        $both = [$registration_1->id, $registration_2->id];
        sort($both);

        $this->login($this->userDirector->createUser('admin'));
        $this->assertSame($both, $this->findIds($params, Registration::class), 'Admin deve filtrar por campo do formulário em todas as inscrições.');

        $this->login($opportunity_owner);
        $this->assertSame($both, $this->findIds($params, Registration::class), 'Dono da oportunidade deve filtrar por campo do formulário em todas as inscrições dela.');

        $this->login($registration_1->owner->user);
        $this->assertSame([$registration_1->id], $this->findIds($params, Registration::class), 'Inscrito deve filtrar por campo do formulário só na própria inscrição.');

        $valuer_1 = $app->repo('User')->find(array_keys($registration_1->valuers)[0]);
        $valuer_2 = $app->repo('User')->find(array_keys($registration_2->valuers)[0]);
        $this->assertNotEquals($valuer_1->id, $valuer_2->id, 'Cada inscrição deve ter um avaliador diferente para o teste fazer sentido.');

        $this->login($valuer_1);
        $this->assertSame([$registration_1->id], $this->findIds($params, Registration::class), 'Avaliador deve filtrar por campo do formulário só nas inscrições atribuídas a ele.');

        $this->login($this->userDirector->createUser());
        $this->assertSame([], $this->findIds($params, Registration::class), 'Usuário sem permissão não deve filtrar por campo do formulário.');

        $this->logout();
        $this->assertSame([], $this->findIds($params, Registration::class), 'Visitante não deve filtrar por campo do formulário.');
    }

    function testUserPrivateMetadataFilterRespectsPermissions()
    {
        $app = App::i();
        $user_1 = $this->userDirector->createUser();
        $user_2 = $this->userDirector->createUser();

        $app->disableAccessControl();
        foreach ([$user_1, $user_2] as $user) {
            $user->deleteAccountToken = 'token-teste';
            $user->save(true);
        }
        $app->enableAccessControl();
        $this->processPCache();

        $params = ['id' => "IN({$user_1->id},{$user_2->id})", 'deleteAccountToken' => 'EQ(token-teste)'];

        $this->login($this->userDirector->createUser('admin'));
        $this->assertSame([$user_1->id, $user_2->id], $this->findIds($params, User::class), 'Admin deve filtrar usuários por metadado privado.');

        $this->login($user_1);
        $this->assertSame([$user_1->id], $this->findIds($params, User::class), 'Usuário deve filtrar por metadado privado só a si mesmo.');

        $this->logout();
        $this->assertSame([], $this->findIds($params, User::class), 'Visitante não deve filtrar usuários por metadado privado.');
    }
}
