<?php

namespace Test;

use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Agent;
use Tests\Abstract\TestCase;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class ApiTest extends TestCase
{
    use UserDirector;

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

    function testApiKeywordWithSingleQuota()
    {
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

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,name,nomeCompleto',
            '@keyword' => 'Um',
            '@order' => 'id ASC'
        ]);

        $result = $query->find();
        $this->assertEquals(1, count($result), 'Certificando que a busca na api por palavra-chave simples retorna o número correto de resultados.');

        $this->app->enableAccessControl();
    }

    function testAgentApiKeywordCNPJ()
    {
        $this->app->disableAccessControl();

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $agents = [
            [
                'name' => 'Fulano',
                'nomeCompleto' => 'Fulano de Tal',
                'cnpj' => '63.090.308/0001-95',

            ],
            [
                'name' => 'Ciclano',
                'nomeCompleto' => 'Ciclano de Catatau',
                'cnpj' => '55.804.960/0001-04',

            ],
            [
                'name' => 'Beltrano',
                'nomeCompleto' => 'Beltrano\'s Um',
                'cnpj' => null,

            ],
            [
                'name' => 'Beltrano',
                'nomeCompleto' => 'Beltrano\'s Dois',
                'cnpj' => null,

            ],

        ];

        foreach ($agents as $agent) {
            $user = $this->userDirector->createUser();
            $profile = $user->profile;
            $profile->name = $agent['name'];
            $profile->nomeCompleto = $agent['nomeCompleto'];
            if ($agent['cnpj']) {
                $profile->cnpj = $agent['cnpj'];
            }
            $profile->save(true);
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,name,nomeCompleto,cnpj',
            '@keyword' => '55.804.960/0001-04',
            '@order' => 'id ASC'
        ]);

        $result = $query->find();

        $this->assertEquals(1, count($result), 'Certificando que a busca por cnpj na api de agente por palavra-chave retorna o número correto de resultados.');

        $this->assertEquals($result[0]['cnpj'], '55.804.960/0001-04', 'Certificando que a busca por cnpj na api de agente por palavra-chave retorn o resultado correto.');
    }

    function testAgentApiKeywordTAG()
    {
        $this->app->disableAccessControl();

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $agents = [
            [
                'name' => 'Fulano',
                'nomeCompleto' => 'Fulano de Tal',
                'tags' => 'tag 1,tag 2,tag 3',
            ],
            [
                'name' => 'Ciclano',
                'nomeCompleto' => 'Ciclano de Catatau',
                'tags' => 'tag 1,tag 3',
            ],
            [
                'name' => 'Beltrano',
                'nomeCompleto' => 'Beltrano\'s Um',
                'tags' => 'tag 1,outra tag',
            ],
            [
                'name' => 'Beltrano',
                'nomeCompleto' => 'Beltrano\'s Dois',
                'tags' => null,
            ],

        ];

        foreach ($agents as $agent) {
            $user = $this->userDirector->createUser();
            $profile = $user->profile;
            $profile->name = $agent['name'];
            $profile->nomeCompleto = $agent['nomeCompleto'];
            if ($agent['tags']) {
                $profile->terms = ['tag' => explode(',', $agent['tags'])];
            }
            $profile->save(true);
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,name,nomeCompleto',
            '@keyword' => 'tag 1',
            '@order' => 'id ASC'
        ]);
        $result = $query->find();
        $this->assertEquals(3, count($result), 'Certificando que a busca por tag na api de agente por palavra-chave retorna o número correto de resultados.');

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,name,nomeCompleto',
            '@keyword' => 'tag 2',
            '@order' => 'id ASC'
        ]);
        $result = $query->find();
        $this->assertEquals(1, count($result), 'Certificando que a busca por tag na api de agente por palavra-chave retorna o número correto de resultados.');

        $query = new ApiQuery(Agent::class, [
            '@select' => 'id,name,nomeCompleto',
            '@keyword' => 'tag 3',
            '@order' => 'id ASC'
        ]);
        $result = $query->find();
        $this->assertEquals(2, count($result), 'Certificando que a busca por tag na api de agente por palavra-chave retorna o número correto de resultados.');
    }
}
