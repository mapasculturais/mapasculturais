<?php

namespace Tests;

use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\UserDirector;

class AgentApiTest extends TestCase
{
    use UserDirector,
        AgentDirector;

    function testFilterAgentsByAvatar()
    {
        $app = App::i();
        
        $user = $this->userDirector->createUser();
        $user_2 = $this->userDirector->createUser();

        $app->disableAccessControl();
        $agent_with_avatar = $this->agentDirector->createAgent($user, 1, fill_requered_properties: true, save: true, use_avatar: true);
        $agent_without_avatar = $this->agentDirector->createAgent($user_2, 1, fill_requered_properties: true, save: true);
        $app->enableAccessControl();

        $query_with_avatar = new ApiQuery(Agent::class, [
            '@select' => 'id',
            'avatar' => 'EQ(1)',
            '@order' => 'id ASC',
        ]);

        $result_with_avatar = $query_with_avatar->find();

        $this->assertCount(1, $result_with_avatar, 'A API deve retornar apenas agentes que possuem avatar quando filtrado por avatar=EQ(1).');
        $this->assertEquals($agent_with_avatar->id, $result_with_avatar[0]['id'], 'O agente retornado deve ser o que possui avatar.');

        $query_without_avatar = new ApiQuery(Agent::class, [
            '@select' => 'id',
            'avatar' => 'EQ(0)',
            '@order' => 'id ASC',
        ]);

        $result_without_avatar = $query_without_avatar->find();
        $result_without_avatar_ids = array_column($result_without_avatar, 'id');

        $this->assertContains($agent_without_avatar->id, $result_without_avatar_ids, 'O resultado deve incluir o agente sem avatar quando filtrado por avatar=EQ(0).');
        $this->assertNotContains($agent_with_avatar->id, $result_without_avatar_ids, 'O resultado não deve incluir o agente com avatar quando filtrado por avatar=EQ(0).');
    }
}
