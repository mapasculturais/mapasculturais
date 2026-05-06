<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Exceptions\Halt;
use PersonalAccessToken\Controllers\PersonalAccessTokenController;
use PersonalAccessToken\Entities\PersonalAccessToken;
use Slim\Psr7\Response;
use Tests\Traits\Faker;
use Tests\Traits\PersonalAccessTokenDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class PersonalAccessTokenControllerTest extends Abstract\TestCase
{
    use UserDirector,
        PersonalAccessTokenDirector,
        RequestFactory,
        Faker;

    private function invokePOST_index(array $postData): void
    {
        $app = App::i();
        $app->response = new Response();

        $psr7 = $this->requestFactory->POST('personal-access-token', 'index', [], $postData);
        $app->request = $this->requestFactory->mapasPOST(
            'personal-access-token',
            'index',
            [],
            [],
            $postData
        );

        $controller = $app->controller('personal-access-token');
        $controller->postData = $postData;

        try {
            $controller->POST_index();
        } catch (Halt) {
        }
    }

    function testPOST_indexCreatesToken()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->invokePOST_index([
            'name' => 'Meu Token de Teste',
            'permissions' => ['agent.view', 'agent.modify'],
        ]);

        $this->assertEquals(201, $this->app->response->getStatusCode(), 'POST deve retornar 201');

        $body = json_decode((string) $this->app->response->getBody(), true);
        $this->assertNotEmpty($body, 'Response body não deve ser vazio');
        $this->assertArrayHasKey('plainTextToken', $body, 'Response deve conter plainTextToken');
        $this->assertArrayHasKey('id', $body, 'Response deve conter id');
        $this->assertStringStartsWith('mc_pat_', $body['plainTextToken'], 'Token deve ter o prefixo');
        $this->assertEquals('Meu Token de Teste', $body['name'], 'Nome deve ser igual ao enviado');
    }

    function testPOST_indexRequiresAuthentication()
    {
        $this->logout();

        $this->invokePOST_index([
            'name' => 'Test',
            'permissions' => ['*'],
        ]);

        $this->assertEquals(401, $this->app->response->getStatusCode(), 'POST sem auth deve retornar 401');
    }

    function testPOST_indexRejectsEmptyName()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->invokePOST_index([
            'name' => '',
            'permissions' => ['*'],
        ]);

        $this->assertEquals(400, $this->app->response->getStatusCode());
    }

    function testPOST_indexRejectsShortName()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->invokePOST_index([
            'name' => 'AB',
            'permissions' => ['*'],
        ]);

        $this->assertEquals(400, $this->app->response->getStatusCode());
    }

    function testPOST_indexRejectsEmptyPermissions()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->invokePOST_index([
            'name' => 'Token válido',
            'permissions' => [],
        ]);

        $this->assertEquals(400, $this->app->response->getStatusCode());
    }

    function testPOST_indexRejectsInvalidPermissions()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->invokePOST_index([
            'name' => 'Token válido',
            'permissions' => ['not_a_valid_permission_format'],
        ]);

        $this->assertEquals(400, $this->app->response->getStatusCode());
    }

    function testPOST_indexAcceptsValidExpiryDate()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $future = (new \DateTime('+30 days'))->format('Y-m-d H:i:s');

        $this->invokePOST_index([
            'name' => 'Token com expiração',
            'permissions' => ['*'],
            'expiresAt' => $future,
        ]);

        $this->assertEquals(201, $this->app->response->getStatusCode());
        $body = json_decode((string) $this->app->response->getBody(), true);
        $this->assertNotEmpty($body['expiresAt'], 'expiresAt deve estar preenchido');
    }

    function testPOST_indexRejectsPastExpiryDate()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $past = (new \DateTime('-1 day'))->format('Y-m-d H:i:s');

        $this->invokePOST_index([
            'name' => 'Token com expiração passada',
            'permissions' => ['*'],
            'expiresAt' => $past,
        ]);

        $this->assertEquals(400, $this->app->response->getStatusCode());
    }

    function testPOST_indexSanitizesPermissions()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->invokePOST_index([
            'name' => 'Token misto',
            'permissions' => ['agent.view', 'invalid', 'space.modify', 123, 'space.*'],
        ]);

        $this->assertEquals(201, $this->app->response->getStatusCode());
        $body = json_decode((string) $this->app->response->getBody(), true);

        $this->assertContains('agent.view', $body['permissions']);
        $this->assertContains('space.modify', $body['permissions']);
        $this->assertContains('space.*', $body['permissions']);
        $this->assertNotContains('invalid', $body['permissions']);
        $this->assertNotContains(123, $body['permissions']);
    }

    function testPOST_indexAcceptsGlobalWildcard()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->invokePOST_index([
            'name' => 'Token global',
            'permissions' => ['*'],
        ]);

        $this->assertEquals(201, $this->app->response->getStatusCode());
    }

    function testPOST_indexCreatesTokenForLoggedInUser()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->invokePOST_index([
            'name' => 'Token de usuário',
            'permissions' => ['agent.view'],
        ]);

        $body = json_decode((string) $this->app->response->getBody(), true);
        $token = $this->app->em->find(PersonalAccessToken::class, $body['id']);
        $this->assertEquals($user->id, $token->user->id, 'Token deve pertencer ao usuário logado');
    }
}
