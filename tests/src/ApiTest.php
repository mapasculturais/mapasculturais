<?php

namespace Test;

use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\User;
use Tests\Abstract\TestCase;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\SpaceDirector;
use Tests\Traits\UserDirector;

class ApiTest extends TestCase
{
    use UserDirector,
        SpaceDirector;

    function testInMultiselectMetadata()
    {
        $this->app->disableAccessControl();
        /* valores válidos para pessoaDeficiente:
                - Nenhuma
                - Auditiva
                - Física-motora
                - Intelectual
                - Múltipla
                - Transtorno do Espectro Autista
                - Visual
                - Outras
        */
        $values = [
            ['Auditiva', 'Visual'],
            ['Auditiva', 'Visual', 'Transtorno do Espectro Autista'],
            ['Transtorno do Espectro Autista', 'Outras'],
            ['Transtorno do Espectro Autista', 'Outras', 'Múltipla'],
            ['Auditiva', 'Múltipla'],
            ['Intelectual']
        ];
        

        foreach ($values as $i => $vs) {
            $user = $this->userDirector->createUser();
            $profile = $user->profile;
            $profile->pessoaDeficiente = $vs;
            $profile->save(true);
        }

        $this->processPCache();

        // testando a busca por 1 termo
        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,pessoaDeficiente',
            'pessoaDeficiente' => API::IN(['Auditiva']),
            '@order' => 'id ASC'
        ]);
        $result = $query->find();
        
        $this->assertEquals(3, count($result), 'Certificando que a busca na api, quanto utilizado o operador IN em metadados de seleção múltipla, retorna a quantidade correta de resultados - busca por 1 termo');

        foreach ($result as $agent) {
            $this->assertContains('Auditiva', $agent['pessoaDeficiente'], 'Certificando que todos os resultados da consulta na api, quanto utilizado o operador IN em metadados de seleção múltipla, contém o termo buscado - busca por 1 termo');
        }


        // testando a busca por 2 termos
        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,name,pessoaDeficiente',
            'pessoaDeficiente' => API::IN(['Auditiva', 'Múltipla']),
            '@order' => 'id ASC'
        ]);
        $result = $query->find();
        $this->assertEquals(4, count($result), 'Certificando que a busca na api, quanto utilizado o operador IN em metadados de seleção múltipla, retorna a quantidade correta de resultados - busca por 2 termos');

        foreach ($result as $agent) {
            $this->assertContainsOneOf(['Auditiva', 'Múltipla'], $agent['pessoaDeficiente'], 'Certificando que todos os resultados da consulta na api, quanto utilizado o operador IN em metadados de seleção múltipla, contém ao menos um dos termo buscados - busca por 2 termos');
        }

        $this->app->enableAccessControl();
    }

    function testApiKeywordWithSingleQuota() {
        $this->app->disableAccessControl();

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $names = [
            ['Fulano', 'Fulano de Tal'],
            ['Ciclano', 'Ciclano de Catatau'],
            ['Beltrano', 'Beltrano\'s Um'],
            ['Beltrano', 'Beltrano\'s Dois'],
        ];

        foreach ($names as $name) {
            $user = $this->userDirector->createUser();
            $profile = $user->profile;
            $profile->name = $name[0];
            $profile->nomeCompleto = $name[1];
            $profile->save(true);
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,name,nomeCompleto',
            '@keyword' => 'Beltrano\'s',
            '@order' => 'id ASC'
        ]);

        $result = $query->find();
        $this->assertEquals(2, count($result), 'Certificando que a busca na api por palavra-chave com aspas simples retorna o número correto de resultados.');

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,name,nomeCompleto',
            '@keyword' => 'Beltrano\'s Um; Beltrano\'s Dois',
            '@order' => 'id ASC'
        ]);

        $result = $query->find();
        $this->assertEquals(2, count($result), 'Certificando que a busca na api por palavra-chave com dois termos separados por ponto e vírgula e com aspas simples retorna o número correto de resultados.');


        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,name,nomeCompleto',
            '@keyword' => 'Beltrano\'s Um',
            '@order' => 'id ASC'
        ]);

        $result = $query->find();
        $this->assertEquals(1, count($result), 'Certificando que a busca na api por palavra-chave com aspas simples retorna o número correto de resultados.');

        $this->app->enableAccessControl();
    }

    function testAgentApiReturnsUserMetadata()
    {
        for ($i = 0; $i < 2; $i++) {
            $user = $this->userDirector->createUser();
            $this->spaceDirector->createSpace($user->profile, disable_access_control: true);
        }

        $queries = [
            Agent::class => (object) [
                'api_name' => 'agentes',
                'user_cb' => function ($result) {return $result['user']; },
                'queries' => [
                    'name,user.*',
                    'name,user.{authUid,email,deleteAccountToken,profile,currentUserPermissions,status}'
                ]
            ],
            Space::class => (object) [
                'api_name' => 'espaços',
                'user_cb' => function ($result) {return $result['owner']['user']; },
                'queries' => [
                    'name,owner.user.*',
                    'name,owner.user.{authUid,email,deleteAccountToken,profile,currentUserPermissions,status}'
                ]
            ],
        ];

        $allowed_user_properties = User::getPublicApiFields();

        foreach($queries as $entity_class => $def) {
            $cb = $def->user_cb;

            foreach($def->queries as $query_select) {
                $query = new ApiQuery($entity_class, [
                    '@select' => $query_select
                ]);
        
                $result = $query->find();
                
                $user = $cb($result[1]);

                $returned_allowed_metadata = [];
                $returned_not_allowed_metadata = [];
        
                foreach($user as $key => $value) {
                    if($key == "@entityType") {
                        continue;
                    }

                    if(in_array($key, $allowed_user_properties)) {
                        $returned_allowed_metadata[$key] = $value;
                    } else {
                        $returned_not_allowed_metadata[$key] = $value;
                    }
                }
                $api_name = $def->api_name;

                $this->assertCount(count($allowed_user_properties), $returned_allowed_metadata, "Certificando que a api de {$api_name} retornar TODOS os metadados permitidos");
        
                $this->assertEmpty($returned_not_allowed_metadata, "Certificando que a api de {$api_name} NÃO retorna os metadados não permitidos");
            }
        }
    }
}
