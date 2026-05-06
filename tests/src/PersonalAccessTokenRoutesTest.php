<?php

namespace Tests;

use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use PersonalAccessToken\AuthProviders\PATAuthProvider;
use PersonalAccessToken\Entities\PersonalAccessToken;
use Tests\Traits\Faker;
use Tests\Traits\PersonalAccessTokenDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class PersonalAccessTokenRoutesTest extends Abstract\TestCase
{
    use UserDirector,
        PersonalAccessTokenDirector,
        RequestFactory,
        Faker;

    function testApiQueryReturnsOnlyOwnTokens()
    {
        $user1 = $this->userDirector->createUser();
        $user2 = $this->userDirector->createUser();

        $this->app->disableAccessControl();
        $token1 = $this->personalAccessTokenDirector->createToken($user1, ['agent.view']);
        $token2 = $this->personalAccessTokenDirector->createToken($user1, ['space.modify']);
        $token3 = $this->personalAccessTokenDirector->createToken($user2, ['event.create']);
        $this->app->enableAccessControl();

        $this->login($user1);

        $query = new ApiQuery(PersonalAccessToken::class, [
            '@select' => 'id,name,permissions',
        ]);

        $results = $query->find();

        $this->assertCount(2, $results, 'ApiQuery deve retornar apenas tokens do usuário logado');
        $ids = array_column($results, 'id');
        $this->assertContains($token1->id, $ids);
        $this->assertContains($token2->id, $ids);
        $this->assertNotContains($token3->id, $ids);
    }

    function testApiQueryReturnsEmptyForGuest()
    {
        $user = $this->userDirector->createUser();

        $this->app->disableAccessControl();
        $this->personalAccessTokenDirector->createToken($user);
        $this->app->enableAccessControl();

        $this->logout();

        $query = new ApiQuery(PersonalAccessToken::class, [
            '@select' => 'id',
        ]);

        $results = $query->find();

        $this->assertEmpty($results, 'ApiQuery não deve retornar tokens para guests');
    }

    function testApiQueryReturnsEmptyForOtherUser()
    {
        $user1 = $this->userDirector->createUser();
        $user2 = $this->userDirector->createUser();

        $this->app->disableAccessControl();
        $this->personalAccessTokenDirector->createToken($user1);
        $this->app->enableAccessControl();

        $this->login($user2);

        $query = new ApiQuery(PersonalAccessToken::class, [
            '@select' => 'id',
        ]);

        $results = $query->find();

        $this->assertEmpty($results, 'ApiQuery não deve retornar tokens de outro usuário');
    }

    function testDELETE_softDeletesToken()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $this->app->disableAccessControl();
        $token = $this->personalAccessTokenDirector->createToken($user);
        $this->app->enableAccessControl();

        $request = $this->requestFactory->DELETE(
            'personal-access-token',
            'single',
            [$token->id]
        );

        $this->assertStatus200($request, 'DELETE de token próprio deve retornar 200');
    }

    function testDELETE_disallowsOtherUser()
    {
        $user1 = $this->userDirector->createUser();
        $user2 = $this->userDirector->createUser();

        $this->app->disableAccessControl();
        $token = $this->personalAccessTokenDirector->createToken($user1);
        $this->app->enableAccessControl();

        $this->login($user2);

        $request = $this->requestFactory->DELETE(
            'personal-access-token',
            'single',
            [$token->id]
        );

        $this->assertStatus403($request, 'DELETE de token de outro usuário deve retornar 403');
    }

    function testPATAuthProviderIntegration()
    {
        $user = $this->userDirector->createUser();
        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user, ['agent.view']);

        $auth = new PATAuthProvider(['token' => $result['plainText']]);

        $this->assertTrue($auth->isUserAuthenticated());
        $this->assertEquals($user->id, $auth->_getAuthenticatedUser()->id);
        $this->assertNotNull($auth->getTokenEntity());
        $this->assertEquals(['agent.view'], $auth->getTokenEntity()->getPermissions());
    }

    function testPATWithGlobalWildcardHasAllPermissions()
    {
        $user = $this->userDirector->createUser();
        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user, ['*']);

        $auth = new PATAuthProvider(['token' => $result['plainText']]);
        $tokenEntity = $auth->getTokenEntity();

        $this->assertTrue($tokenEntity->hasPermission('*'));
        $this->assertEquals(['*'], $tokenEntity->getPermissions());
    }
}
