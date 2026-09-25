<?php

namespace Tests;

use MapasCulturais\Entities\User;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\UserDirector;

class UserManagementFilterTest extends TestCase
{
    use UserDirector,
        AgentDirector;

    function testFilterUsersByCPFIndividual()
    {
        $this->app->disableAccessControl();

        // Cria um usuário
        $user = $this->userDirector->createUser();

        // Cria 2 agentes individuais (tipo 1) sem parent_id
        $cpf1 = '123.456.789-00';
        $cpf2 = '987.654.321-00';

        $agent1 = $this->agentDirector->createAgent($user, 1, fill_requered_properties: true, save: false);
        $agent1->cpf = $cpf1;
        $agent1->save(true);
        $agent1->setParentAsNull(true);

        $agent2 = $this->agentDirector->createAgent($user, 1, fill_requered_properties: true, save: false);
        $agent2->cpf = $cpf2;
        $agent2->save(true);
        $agent2->setParentAsNull(true);

        // Garante que ambos não têm parent_id
        $this->assertNull($agent1->parent, 'Agente 1 não deve ter parent_id');
        $this->assertNull($agent2->parent, 'Agente 2 não deve ter parent_id');

        // Remove o metadado documento se existir (ele pode ter sido criado automaticamente pelo CPF)
        $agent1 = $agent1->refreshed();
        $doc_meta1 = $this->app->repo('AgentMeta')->findOneBy(['owner' => $agent1, 'key' => 'documento']);
        if ($doc_meta1) {
            $doc_meta1->delete(true);
        }

        $agent2 = $agent2->refreshed();
        $doc_meta2 = $this->app->repo('AgentMeta')->findOneBy(['owner' => $agent2, 'key' => 'documento']);
        if ($doc_meta2) {
            $doc_meta2->delete(true);
        }


        // Testa o filtro pelo CPF do primeiro agente
        $repo = $this->app->repo('User');
        $userIds = $repo->getIdsByKeyword(preg_replace("/\D/", '', $cpf1));
        $this->assertEquals([$user->id], $userIds, 'O filtro por CPF deve retornar apenas o usuário correto');

        // Testa o filtro pelo CPF do segundo agente
        $userIds2 = $repo->getIdsByKeyword(preg_replace("/\D/", '', $cpf2));
        $this->assertEquals([$user->id], $userIds2, 'O filtro por CPF do segundo agente deve retornar apenas o usuário correto');

        $this->app->enableAccessControl();
    }

    function testFilterUsersByCNPJCollective()
    {
        $this->app->disableAccessControl();

        // Cria um usuário
        $user = $this->userDirector->createUser();

        // Cria 2 agentes coletivos (tipo 2) sem parent_id
        $cnpj1 = '12.345.678/0001-90';
        $cnpj2 = '98.765.432/0001-10';

        $agent1 = $this->agentDirector->createAgent($user, 2, fill_requered_properties: true, save: false);
        $agent1->cnpj = $cnpj1;
        $agent1->save(true);
        $agent1->setParentAsNull(true);

        $agent2 = $this->agentDirector->createAgent($user, 2, fill_requered_properties: true, save: false);
        $agent2->cnpj = $cnpj2;
        $agent2->save(true);
        $agent2->setParentAsNull(true);

        // Garante que ambos não têm parent_id
        $this->assertNull($agent1->parent, 'Agente 1 não deve ter parent_id');
        $this->assertNull($agent2->parent, 'Agente 2 não deve ter parent_id');

        // Remove o metadado documento se existir (ele pode ter sido criado automaticamente pelo CNPJ)
        $agent1 = $agent1->refreshed();
        $doc_meta1 = $this->app->repo('AgentMeta')->findOneBy(['owner' => $agent1, 'key' => 'documento']);
        if ($doc_meta1) {
            $doc_meta1->delete(true);
        }

        $agent2 = $agent2->refreshed();
        $doc_meta2 = $this->app->repo('AgentMeta')->findOneBy(['owner' => $agent2, 'key' => 'documento']);
        if ($doc_meta2) {
            $doc_meta2->delete(true);
        }

        // Testa o filtro pelo CNPJ do primeiro agente
        $repo = $this->app->repo('User');
        $userIds = $repo->getIdsByKeyword(preg_replace("/\D/", '', $cnpj1));
        $this->assertEquals([$user->id], $userIds, 'O filtro por CNPJ deve retornar apenas o usuário correto');

        // Testa o filtro pelo CNPJ do segundo agente
        $userIds2 = $repo->getIdsByKeyword(preg_replace("/\D/", '', $cnpj2));
        $this->assertEquals([$user->id], $userIds2, 'O filtro por CNPJ do segundo agente deve retornar apenas o usuário correto');

        $this->app->enableAccessControl();
    }
}

