<?php

namespace Test;

use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Exceptions\Api\InvalidArgument;
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

    // ================================================================
    // distinct() tests
    // ================================================================

    function testDistinctSingleProperty()
    {
        $this->app->disableAccessControl();

        $names = ['Fulano', 'Fulano', 'Ciclano'];
        $ids = [];
        foreach ($names as $name) {
            $user = $this->userDirector->createUser();
            $user->profile->name = $name;
            $user->profile->save(true);
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name',
            '@order' => 'name ASC',
            'id' => API::IN($ids),
        ]);

        $result = $query->distinct();

        $this->assertTrue(is_array($result) && !empty($result), 'Certificando que distinct retorna um array não vazio');
        $this->assertFalse(isset($result[0]['name']), 'Certificando que distinct com campo único retorna array simples, não de arrays');
        $this->assertEquals(['Ciclano', 'Fulano'], array_values($result), 'Certificando que distinct com campo único retorna valores distintos ordenados');

        $this->app->enableAccessControl();
    }

    function testDistinctMultipleProperties()
    {
        $this->app->disableAccessControl();

        $agents = [
            ['name' => 'Fulano', 'status' => 1],
            ['name' => 'Fulano', 'status' => -5],
            ['name' => 'Ciclano', 'status' => 1],
        ];
        $ids = [];
        foreach ($agents as $data) {
            $user = $this->userDirector->createUser();
            $user->profile->name = $data['name'];
            $user->profile->status = $data['status'];
            $user->profile->save(true);
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name,status',
            '@order' => 'name ASC',
            'id' => API::IN($ids),
            'status' => 'GTE(-10)',
        ]);

        $result = $query->distinct();

        $this->assertCount(3, $result, 'Certificando que distinct com múltiplos campos retorna combinações distintas');
        $this->assertArrayHasKey('name', $result[0], 'Certificando que cada resultado tem a chave name');
        $this->assertArrayHasKey('status', $result[0], 'Certificando que cada resultado tem a chave status');

        $combinations = array_map(fn($r) => $r['name'] . ':' . $r['status'], $result);
        $this->assertCount(3, array_unique($combinations), 'Certificando que todas combinações são únicas');

        $this->app->enableAccessControl();
    }

    function testDistinctWithMetadata()
    {
        $this->app->disableAccessControl();

        $user1 = $this->userDirector->createUser();
        $user1->profile->pessoaDeficiente = ['Auditiva', 'Visual'];
        $user1->profile->save(true);

        $user2 = $this->userDirector->createUser();
        $user2->profile->pessoaDeficiente = ['Auditiva'];
        $user2->profile->save(true);

        $user3 = $this->userDirector->createUser();
        $user3->profile->pessoaDeficiente = ['Visual'];
        $user3->profile->save(true);

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'pessoaDeficiente',
        ]);

        $result = $query->distinct();

        $this->assertTrue(is_array($result), 'Certificando que distinct com metadado retorna array');
        $this->assertGreaterThanOrEqual(1, count($result), 'Certificando que distinct com metadado retorna pelo menos 1 resultado');

        $this->app->enableAccessControl();
    }

    function testDistinctWithPropertyAndMetadata()
    {
        $this->app->disableAccessControl();

        $ids = [];

        $user1 = $this->userDirector->createUser();
        $user1->profile->name = 'Fulano';
        $user1->profile->pessoaDeficiente = ['Auditiva'];
        $user1->profile->save(true);
        $ids[] = $user1->profile->id;

        $user2 = $this->userDirector->createUser();
        $user2->profile->name = 'Ciclano';
        $user2->profile->pessoaDeficiente = ['Visual'];
        $user2->profile->save(true);
        $ids[] = $user2->profile->id;

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name,pessoaDeficiente',
            '@order' => 'name ASC',
            'id' => API::IN($ids),
        ]);

        $result = $query->distinct();

        $this->assertCount(2, $result, 'Certificando que distinct com propriedade + metadado retorna combinações distintas');
        $this->assertArrayHasKey('name', $result[0], 'Certificando que resultado tem name');
        $this->assertArrayHasKey('pessoaDeficiente', $result[0], 'Certificando que resultado tem pessoaDeficiente');

        $this->app->enableAccessControl();
    }

    function testDistinctWithFilters()
    {
        $this->app->disableAccessControl();

        $names = ['Fulano', 'Fulano', 'Ciclano', 'Beltrano'];
        foreach ($names as $name) {
            $user = $this->userDirector->createUser();
            $user->profile->name = $name;
            $user->profile->save(true);
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name',
            'name' => 'ILIKE(Fulano%)',
            '@order' => 'name ASC'
        ]);

        $result = $query->distinct();

        $this->assertEquals(['Fulano'], $result, 'Certificando que distinct com filtro retorna apenas valores que match o filtro');

        $this->app->enableAccessControl();
    }

    function testDistinctInvalidFieldThrowsError()
    {
        $this->app->disableAccessControl();

        $this->expectException(InvalidArgument::class);

        $query = new ApiQuery(Agent::class, [
            '@select' => 'files',
        ]);

        $query->distinct();

        $this->app->enableAccessControl();
    }

    function testDistinctWithType()
    {
        $this->app->disableAccessControl();

        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $user = $this->userDirector->createUser();
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'type',
            '@order' => 'type ASC',
            'id' => API::IN($ids),
        ]);

        $result = $query->distinct();

        $this->assertTrue(is_array($result), 'Certificando que distinct com type retorna array');
        $this->assertGreaterThanOrEqual(1, count($result), 'Certificando que distinct com type retorna pelo menos 1 resultado');
        foreach ($result as $val) {
            $this->assertIsNumeric($val, 'Certificando que valores de type sao numericos');
        }

        $this->app->enableAccessControl();
    }

    function testDistinctWithOrder()
    {
        $this->app->disableAccessControl();

        $names = ['Beltrano', 'Fulano', 'Ciclano'];
        $ids = [];
        foreach ($names as $name) {
            $user = $this->userDirector->createUser();
            $user->profile->name = $name;
            $user->profile->save(true);
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name',
            '@order' => 'name DESC',
            'id' => API::IN($ids),
        ]);

        $result = $query->distinct();

        $this->assertEquals(['Fulano', 'Ciclano', 'Beltrano'], array_values($result), 'Certificando que distinct respeita @order DESC');

        $this->app->enableAccessControl();
    }

    // ================================================================
    // countGrouped() tests
    // ================================================================

    function testCountGroupedSingleProperty()
    {
        $this->app->disableAccessControl();

        $names = ['Fulano', 'Fulano', 'Fulano', 'Ciclano', 'Ciclano', 'Beltrano'];
        $ids = [];
        foreach ($names as $name) {
            $user = $this->userDirector->createUser();
            $user->profile->name = $name;
            $user->profile->save(true);
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name',
            '@order' => 'name ASC',
            'id' => API::IN($ids),
        ]);

        $result = $query->countGrouped();

        $this->assertTrue(is_array($result), 'Certificando que countGrouped retorna array');
        $this->assertEquals(3, $result['Fulano'], 'Certificando que countGrouped conta Fulano corretamente');
        $this->assertEquals(2, $result['Ciclano'], 'Certificando que countGrouped conta Ciclano corretamente');
        $this->assertEquals(1, $result['Beltrano'], 'Certificando que countGrouped conta Beltrano corretamente');

        $this->app->enableAccessControl();
    }

    function testCountGroupedSinglePropertyNumeric()
    {
        $this->app->disableAccessControl();

        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $user = $this->userDirector->createUser();
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'type',
            '@order' => 'type ASC',
            'id' => API::IN($ids),
        ]);

        $result = $query->countGrouped();

        $this->assertTrue(is_array($result), 'Certificando que countGrouped com type retorna array');
        $total = array_sum($result);
        $this->assertEquals(3, $total, 'Certificando que countGrouped com type soma 3');

        $this->app->enableAccessControl();
    }

    function testCountGroupedMultipleProperties()
    {
        $this->app->disableAccessControl();

        $agents = [
            ['name' => 'Fulano', 'status' => 1],
            ['name' => 'Fulano', 'status' => 1],
            ['name' => 'Fulano', 'status' => -5],
            ['name' => 'Ciclano', 'status' => 1],
        ];
        $ids = [];
        foreach ($agents as $data) {
            $user = $this->userDirector->createUser();
            $user->profile->name = $data['name'];
            $user->profile->status = $data['status'];
            $user->profile->save(true);
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name,status',
            '@order' => 'name ASC',
            'id' => API::IN($ids),
            'status' => 'GTE(-10)',
        ]);

        $result = $query->countGrouped();

        $this->assertCount(3, $result, 'Certificando que countGrouped com múltiplos campos retorna 3 grupos distintos');

        $fulano_s1 = array_filter($result, fn($r) => $r['name'] === 'Fulano' && $r['status'] === 1);
        $this->assertCount(1, $fulano_s1, 'Certificando que existe grupo Fulano+status=1');
        $fulano_s1 = array_values($fulano_s1)[0];
        $this->assertEquals(2, $fulano_s1['@count'], 'Certificando que Fulano+status=1 tem @count=2');

        $fulano_sm5 = array_filter($result, fn($r) => $r['name'] === 'Fulano' && $r['status'] === -5);
        $this->assertCount(1, $fulano_sm5, 'Certificando que existe grupo Fulano+status=-5');
        $fulano_sm5 = array_values($fulano_sm5)[0];
        $this->assertEquals(1, $fulano_sm5['@count'], 'Certificando que Fulano+status=-5 tem @count=1');

        $this->app->enableAccessControl();
    }

    function testCountGroupedWithMetadata()
    {
        $this->app->disableAccessControl();

        $user1 = $this->userDirector->createUser();
        $user1->profile->pessoaDeficiente = ['Auditiva'];
        $user1->profile->save(true);

        $user2 = $this->userDirector->createUser();
        $user2->profile->pessoaDeficiente = ['Auditiva'];
        $user2->profile->save(true);

        $user3 = $this->userDirector->createUser();
        $user3->profile->pessoaDeficiente = ['Visual'];
        $user3->profile->save(true);

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'pessoaDeficiente',
        ]);

        $result = $query->countGrouped();

        $this->assertTrue(is_array($result), 'Certificando que countGrouped com metadado retorna array');

        $this->app->enableAccessControl();
    }

    function testCountGroupedWithOrderCountDesc()
    {
        $this->app->disableAccessControl();

        $names = ['Beltrano', 'Fulano', 'Fulano', 'Fulano', 'Ciclano', 'Ciclano'];
        $ids = [];
        foreach ($names as $name) {
            $user = $this->userDirector->createUser();
            $user->profile->name = $name;
            $user->profile->save(true);
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name',
            '@order' => '@count DESC',
            'id' => API::IN($ids),
        ]);

        $result = $query->countGrouped();

        $keys = array_keys($result);
        $this->assertEquals('Fulano', $keys[0], 'Certificando que @count DESC coloca Fulano (3) primeiro');
        $this->assertEquals('Ciclano', $keys[1], 'Certificando que @count DESC coloca Ciclano (2) segundo');
        $this->assertEquals('Beltrano', $keys[2], 'Certificando que @count DESC coloca Beltrano (1) terceiro');

        $this->app->enableAccessControl();
    }

    function testCountGroupedWithOrderCountAsc()
    {
        $this->app->disableAccessControl();

        $names = ['Beltrano', 'Fulano', 'Fulano', 'Fulano', 'Ciclano', 'Ciclano'];
        $ids = [];
        foreach ($names as $name) {
            $user = $this->userDirector->createUser();
            $user->profile->name = $name;
            $user->profile->save(true);
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name',
            '@order' => '@count ASC',
            'id' => API::IN($ids),
        ]);

        $result = $query->countGrouped();

        $keys = array_keys($result);
        $this->assertEquals('Beltrano', $keys[0], 'Certificando que @count ASC coloca Beltrano (1) primeiro');
        $this->assertEquals('Ciclano', $keys[1], 'Certificando que @count ASC coloca Ciclano (2) segundo');
        $this->assertEquals('Fulano', $keys[2], 'Certificando que @count ASC coloca Fulano (3) terceiro');

        $this->app->enableAccessControl();
    }

    function testCountGroupedInvalidFieldThrowsError()
    {
        $this->app->disableAccessControl();

        $this->expectException(InvalidArgument::class);

        $query = new ApiQuery(Agent::class, [
            '@select' => 'terms',
        ]);

        $query->countGrouped();

        $this->app->enableAccessControl();
    }

    function testCountGroupedDefaultOrderIsCountDesc()
    {
        $this->app->disableAccessControl();

        $names = ['Beltrano', 'Fulano', 'Fulano', 'Fulano', 'Ciclano', 'Ciclano'];
        $ids = [];
        foreach ($names as $name) {
            $user = $this->userDirector->createUser();
            $user->profile->name = $name;
            $user->profile->save(true);
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name',
            'id' => API::IN($ids),
        ]);

        $result = $query->countGrouped();

        $keys = array_keys($result);
        $this->assertEquals('Fulano', $keys[0], 'Certificando que default order é @count DESC (Fulano primeiro)');
        $this->assertEquals('Ciclano', $keys[1], 'Certificando que default order é @count DESC (Ciclano segundo)');
        $this->assertEquals('Beltrano', $keys[2], 'Certificando que default order é @count DESC (Beltrano terceiro)');

        $this->app->enableAccessControl();
    }

    function testCountGroupedWithFilters()
    {
        $this->app->disableAccessControl();

        $names = ['Fulano', 'Fulano', 'Ciclano'];
        $ids = [];
        foreach ($names as $name) {
            $user = $this->userDirector->createUser();
            $user->profile->name = $name;
            $user->profile->save(true);
            $ids[] = $user->profile->id;
        }

        $this->processPCache();

        $query = new ApiQuery(Agent::class, [
            '@select' => 'name',
            'name' => 'ILIKE(Fulano%)',
            'id' => API::IN($ids),
        ]);

        $result = $query->countGrouped();

        $this->assertCount(1, $result, 'Certificando que countGrouped com filtro retorna apenas 1 grupo');
        $this->assertEquals(2, $result['Fulano'], 'Certificando que countGrouped com filtro conta corretamente');

        $this->app->enableAccessControl();
    }
}
