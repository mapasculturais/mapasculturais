<?php

namespace Tests;

use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\UserDirector;

class PrivateEntityTest extends TestCase
{
    use UserDirector,
        OpportunityBuilder,
        AgentDirector;

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

    function testPermissionToView()
    {
        $normal_user1 = $this->userDirector->createUser();
        $normal_user2 = $this->userDirector->createUser();
        $normal_user3 = $this->userDirector->createUser();

        $admin = $this->userDirector->createUser('admin');

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

        $this->app->disableAccessControl();
        $opportunity->createAgentRelation($normal_user3->profile, 'teste');
        $this->app->enableAccessControl();

        $this->login($normal_user3);
        $this->assertTrue($opportunity->canUser('view'), 'Garantindo que um usuário consegue ver uma oportunidade privada na qual ele foi relacionado');
    }

    protected function asserApiQueryCount(array $query_params, int $count, string $message)
    {
        $query = new ApiQuery(Opportunity::class, $query_params);

        $result = $query->getFindResult();

        // eval(\psy\sh());

        $this->assertEquals($count, count($result), $message);
    }

    function testAPI() {
       $normal_user1 = $this->userDirector->createUser();
        $normal_user2 = $this->userDirector->createUser();
        $normal_user3 = $this->userDirector->createUser();

        $admin = $this->userDirector->createUser('admin');

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
            $this->asserApiQueryCount($params, 3, "Garantindo que a API retorna as oportunidades privadas para o proprietário das oportunidades");
    
            $this->login($normal_user2);
            $this->asserApiQueryCount($params, 1, "Garantindo que a API NÃO retorna as oportunidades privadas para um usuário comum que não possui vínculo com as oportunidades");
    
            $this->login($admin);
            $this->asserApiQueryCount($params, 3, "Garantindo que a API retorna as oportunidades privadas para os admins");
    
            $this->login($normal_user3);
            $this->asserApiQueryCount($params, 2, "Garantindo que a API retorna as oportunidades privadas para um usuário comum com vínculo com a proprietário");      
        }

        
    }   
}
