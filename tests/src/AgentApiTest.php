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

    function testEvaluationCommitteeSearchFindsAgentByUserEmailAndAgentIdWithoutChangingGlobalKeyword()
    {
        $app = App::i();
        $app->disableAccessControl();

        try {
            $user = $this->userDirector->createUser();
            $user->email = 'avaliador-busca-' . uniqid() . '@example.test';
            $user->save(true);

            $profile = $user->profile;
            $profile->name = 'Pessoa Avaliadora Busca Escopada';
            $profile->save(true);

            $global_query = new ApiQuery(Agent::class, [
                '@select' => 'id,name',
                '@keyword' => $user->email,
                'type' => 'EQ(1)',
                'parent' => 'NULL()',
                '@order' => 'id ASC',
            ]);
            $global_result = $global_query->find();

            $this->assertNotContains(
                $profile->id,
                array_column($global_result, 'id'),
                'A busca global de agentes por @keyword não deve buscar pelo email do usuário.'
            );

            $email_query = new ApiQuery(Agent::class, [
                '@select' => 'id,name,user',
                '@keyword' => $user->email,
                '_evaluatorSearch' => '1',
                'type' => 'EQ(1)',
                'parent' => 'NULL()',
                '@order' => 'id ASC',
            ]);
            $email_result = $email_query->find();

            $this->assertContains(
                $profile->id,
                array_column($email_result, 'id'),
                'A busca de avaliadores deve encontrar agentes individuais pelo email do usuário vinculado.'
            );

            $id_query = new ApiQuery(Agent::class, [
                '@select' => 'id,name',
                '@keyword' => "#{$profile->id}",
                '_evaluatorSearch' => '1',
                'type' => 'EQ(1)',
                'parent' => 'NULL()',
                '@order' => 'id ASC',
            ]);
            $id_result = $id_query->find();

            $this->assertContains(
                $profile->id,
                array_column($id_result, 'id'),
                'A busca de avaliadores deve encontrar agentes individuais pelo ID do agente informado com #.'
            );
        } finally {
            $app->enableAccessControl();
        }
    }

    function testFilterAgentsByAvatar()
    {
        $app = App::i();
        $agent_with_avatar = null;

        $user = $this->userDirector->createUser();
        $user_2 = $this->userDirector->createUser();

        $app->disableAccessControl();
        $agent_with_avatar = $this->agentDirector->createAgent($user, 1, fill_requered_properties: true, save: true, use_avatar: true);
        $agent_without_avatar = $this->agentDirector->createAgent($user_2, 1, fill_requered_properties: true, save: true);
        $app->enableAccessControl();

        // Limpa os arquivos do avatar ao final do teste para não sujar o repo.
        try {
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
        } finally {
            if ($agent_with_avatar) {
                $app->disableAccessControl();

                try {
                    // Garante remoção de qualquer arquivo gerado para este agente (avatar + transforms).
                    $repo = $app->repo($agent_with_avatar->getFileClassName());
                    $files = $repo->findBy(['owner' => $agent_with_avatar]);
                    $paths_to_unlink = [];
                    foreach ($files as $file) {
                        try {
                            $paths_to_unlink[] = $file->getPath();
                        } catch (\Throwable $e) {}
                        try {
                            $file->delete(true);
                        } catch (\Throwable $e) {
                            try {
                                $path = $file->getPath();
                                if (!empty($path) && is_file($path)) @unlink($path);
                            } catch (\Throwable $ignored) {}
                        }
                    }

                    $paths_to_unlink = array_values(array_unique($paths_to_unlink));
                    foreach ($paths_to_unlink as $path) {
                        if (!empty($path) && is_file($path)) {
                            @unlink($path);
                        }
                    }
                } finally {
                    $app->enableAccessControl();
                }
            }
        }
    }
}
