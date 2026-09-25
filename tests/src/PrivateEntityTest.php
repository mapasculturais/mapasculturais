<?php

namespace Tests;

use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Space;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\SpaceDirector;
use Tests\Traits\UserDirector;

class PrivateEntityTest extends TestCase
{
    use UserDirector,
        OpportunityBuilder,
        SpaceDirector,
        AgentDirector;


    protected function assertApiQueryCount(string $entity_class, array $query_params, int $count, string $message)
    {
        $query = new ApiQuery($entity_class, $query_params);

        $result = $query->getFindResult();


        $this->assertEquals($count, count($result), $message);
    }

    protected function createOpportunity(Agent $owner, bool $private): Opportunity
    {
        $current_user = $this->app->user;
        $this->login($owner->user);
    
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $owner, owner_entity: $owner)
            ->fillRequiredProperties()
            ->firstPhase()
            ->setRegistrationPeriod(new Open)
            ->done()
            ->save()
            ->getInstance();
    
        if($private) {
            $opportunity->makePrivate(true);
        }
    
        $this->login($current_user);
    
        return $opportunity;
    }

    protected function createSpace(Agent $owner, bool $private): Space
    {
        $current_user = $this->app->user;
        $this->login($owner->user);
    
        $space = $this->spaceDirector->createSpace(
            owner: $owner,
            private: $private
        );
    
        $this->login($current_user);
    
        return $space;
    }

    function testPermissionToViewOpportunity()
    {
        $normal_user1 = $this->userDirector->createUser();
        $normal_user2 = $this->userDirector->createUser();
        $normal_user3 = $this->userDirector->createUser();

        $admin = $this->userDirector->createUser('admin');
        $saas_admin = $this->userDirector->createUser('saasAdmin');

        $opportunity = $this->createOpportunity(
            owner: $normal_user1->profile,
            private: true
        );

        $this->login($normal_user1);
        $this->assertTrue($opportunity->canUser('view'), 'Garantindo que um usuário consegue ver a própria oportunidade privada');

        $this->login($normal_user2);
        $this->assertFalse($opportunity->canUser('view'), 'Garantindo que um usuário comum NÃO PODE ver uma oportinidade privada de outro usuário');

        $this->login($admin);
        $this->assertTrue($opportunity->canUser('view'), 'Garantindo que um admin PODE ver a uma oportunidade privada de outro usuário');

        $this->login($saas_admin);
        $this->assertTrue($opportunity->canUser('view'), 'Garantindo que um saasAdmin PODE ver a uma oportunidade privada de outro usuário');

        $this->app->disableAccessControl();
        $opportunity->createAgentRelation($normal_user3->profile, 'teste');
        $this->app->enableAccessControl();

        $this->login($normal_user3);
        $this->assertTrue($opportunity->canUser('view'), 'Garantindo que um usuário consegue ver uma oportunidade privada na qual ele foi relacionado');
    }

    function testOppotunityAPI() {
        $normal_user1 = $this->userDirector->createUser();
        $normal_user2 = $this->userDirector->createUser();
        $normal_user3 = $this->userDirector->createUser();

        $admin = $this->userDirector->createUser('admin');
        $saas_admin = $this->userDirector->createUser('saasAdmin');

        $public_opportunity = $this->createOpportunity(
            owner: $normal_user1->profile,
            private: false
        );

        $private_opportunity1 = $this->createOpportunity(
            owner: $normal_user1->profile,
            private: true
        );

        $private_opportunity2 = $this->createOpportunity(
            owner: $normal_user1->profile,
            private: true
        );

    
        $this->app->disableAccessControl();
        $private_opportunity1->createAgentRelation($normal_user3->profile, 'teste');
        $this->app->enableAccessControl();

        $this->processPCache();

        $query_params = [
            ['@select' => 'id,name,ownerEntity.{name},owner.{name},status'],
            ['@select' => 'id,name,status'],
        ];

        foreach($query_params as $params) {
            $this->login($normal_user1);
            $this->assertApiQueryCount(Opportunity::class, $params, 3, "Garantindo que a API retorna as oportunidades privadas para o proprietário das oportunidades");
    
            $this->login($normal_user2);
            $this->assertApiQueryCount(Opportunity::class, $params, 1, "Garantindo que a API NÃO retorna as oportunidades privadas para um usuário comum que não possui vínculo com as oportunidades");
    
            $this->login($admin);
            $this->assertApiQueryCount(Opportunity::class, $params, 3, "Garantindo que a API retorna as oportunidades privadas para os admins");
    
            $this->login($saas_admin);
            $this->assertApiQueryCount(Opportunity::class, $params, 3, "Garantindo que a API retorna as oportunidades privadas para os saasAdmins");
    
            $this->login($normal_user3);
            $this->assertApiQueryCount(Opportunity::class, $params, 2, "Garantindo que a API retorna as oportunidades privadas para um usuário comum com vínculo com a proprietário");      
        }
    }

    function testPermissionToViewSpace()
    {
        $normal_user1 = $this->userDirector->createUser();
        $normal_user2 = $this->userDirector->createUser();
        $normal_user3 = $this->userDirector->createUser();

        $admin = $this->userDirector->createUser('admin');
        $saas_admin = $this->userDirector->createUser('saasAdmin');


        $space = $this->createSpace(
            owner: $normal_user1->profile,
            private: true
        );

        $this->login($normal_user1);
        $this->assertTrue($space->canUser('view'), 'Garantindo que um usuário consegue ver o próprio espaço privado');

        $this->login($normal_user2);
        $this->assertFalse($space->canUser('view'), 'Garantindo que um usuário comum NÃO PODE ver um espaço privado de outro usuário');

        $this->login($admin);
        $this->assertTrue($space->canUser('view'), 'Garantindo que um admin PODE ver a um espaõ privado de outro usuário');

        $this->login($saas_admin);
        $this->assertTrue($space->canUser('view'), 'Garantindo que um saasAdmin PODE ver a um espaõ privado de outro usuário');

        $this->app->disableAccessControl();
        $space->createAgentRelation($normal_user3->profile, 'teste');
        $this->app->enableAccessControl();

        $this->login($normal_user3);
        $this->assertTrue($space->canUser('view'), 'Garantindo que um usuário consegue ver um espaço privado no qual ele foi relacionado');
    }

    function testSpaceAPI() {
        $normal_user1 = $this->userDirector->createUser();
        $normal_user2 = $this->userDirector->createUser();
        $normal_user3 = $this->userDirector->createUser();

        $admin = $this->userDirector->createUser('admin');
        $saas_admin = $this->userDirector->createUser('saasAdmin');

        $public_space = $this->createSpace(
            owner: $normal_user1->profile,
            private: false
        );

        $private_space1 = $this->createSpace(
            owner: $normal_user1->profile,
            private: true
        );

        $private_space2 = $this->createSpace(
            owner: $normal_user1->profile,
            private: true
        );

        $private_space3 = $this->createSpace(
            owner: $admin->profile,
            private: true
        );
    
        $this->app->disableAccessControl();
        $private_space1->createAgentRelation($normal_user3->profile, 'teste');
        $this->app->enableAccessControl();

        $this->processPCache();

        $query_params = [
            ['@select' => 'id,name,owner.{name},status', 'id' => 'GT(0)'],
            ['@select' => 'id,name,status', 'id' => 'GT(0)'],
        ];

        
        foreach($query_params as $params) {
            $this->login($normal_user1);
            $this->assertApiQueryCount(Space::class, $params, 3, "Garantindo que a API retorna os espaços privados para o proprietário dos espaços");
    
            $this->login($normal_user2);
            $this->assertApiQueryCount(Space::class, $params, 1, "Garantindo que a API NÃO retorna os espaços privados para um usuário comum que não possui vínculo com os espaços");
    
            $this->login($admin);
            $this->assertApiQueryCount(Space::class, $params, 4, "Garantindo que a API retorna os espaços privados para os admins");

            $this->login($saas_admin);
            $this->assertApiQueryCount(Space::class, $params, 4, "Garantindo que a API retorna os espaços privados para os saasAdmins");
    
            $this->login($normal_user3);
            $this->assertApiQueryCount(Space::class, $params, 2, "Garantindo que a API retorna os espaços privados para um usuário comum com vínculo com a proprietário");      
        }
    }   
}
