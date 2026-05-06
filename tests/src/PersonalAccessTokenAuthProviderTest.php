<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\GuestUser;
use PersonalAccessToken\AuthProviders\PATAuthProvider;
use PersonalAccessToken\Entities\PersonalAccessToken;
use Tests\Traits\PersonalAccessTokenDirector;
use Tests\Traits\UserDirector;

class PersonalAccessTokenAuthProviderTest extends Abstract\TestCase
{
    use UserDirector,
        PersonalAccessTokenDirector;

    function testValidTokenAuthenticatesUser()
    {
        $user = $this->userDirector->createUser();
        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user);

        $auth = new PATAuthProvider(['token' => $result['plainText']]);

        $this->assertTrue($auth->isUserAuthenticated(), 'Provider deve autenticar para token válido');
        $this->assertEquals($user->id, $auth->_getAuthenticatedUser()->id, 'Provider deve autenticar o usuário correto');
    }

    function testInvalidTokenDoesNotAuthenticate()
    {
        $auth = new PATAuthProvider(['token' => 'mc_pat_invalidtoken123456']);

        $this->assertFalse($auth->isUserAuthenticated(), 'Provider não deve autenticar token inválido');
    }

    function testEmptyTokenDoesNotAuthenticate()
    {
        $auth = new PATAuthProvider(['token' => '']);

        $this->assertFalse($auth->isUserAuthenticated());
    }

    function testWrongPrefixDoesNotAuthenticate()
    {
        $auth = new PATAuthProvider(['token' => 'wrong_prefix_token']);

        $this->assertFalse($auth->isUserAuthenticated());
    }

    function testExpiredTokenDoesNotAuthenticate()
    {
        $user = $this->userDirector->createUser();
        $past = new \DateTime('-1 day');
        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user, ['*'], $past);

        $auth = new PATAuthProvider(['token' => $result['plainText']]);

        $this->assertFalse($auth->isUserAuthenticated(), 'Token expirado não deve autenticar');
    }

    function testDeletedTokenDoesNotAuthenticate()
    {
        $user = $this->userDirector->createUser();
        $this->app->disableAccessControl();

        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user);
        $result['entity']->delete(true);

        $auth = new PATAuthProvider(['token' => $result['plainText']]);

        $this->assertFalse($auth->isUserAuthenticated(), 'Token revogado não deve autenticar');

        $this->app->enableAccessControl();
    }

    function testGetTokenEntityReturnsCorrectEntity()
    {
        $user = $this->userDirector->createUser();
        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user);

        $auth = new PATAuthProvider(['token' => $result['plainText']]);

        $tokenEntity = $auth->getTokenEntity();

        $this->assertNotNull($tokenEntity, 'getTokenEntity deve retornar a entidade');
        $this->assertEquals($result['entity']->id, $tokenEntity->id, 'getTokenEntity deve retornar o token correto');
    }

    function testGetTokenEntityReturnsNullForInvalidToken()
    {
        $auth = new PATAuthProvider(['token' => 'mc_pat_invalid']);

        $this->assertNull($auth->getTokenEntity(), 'getTokenEntity deve retornar null para token inválido');
    }

    function testCleanUserSessionClearsAuthentication()
    {
        $user = $this->userDirector->createUser();
        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user);

        $auth = new PATAuthProvider(['token' => $result['plainText']]);
        $this->assertTrue($auth->isUserAuthenticated());

        $auth->_cleanUserSession();

        $this->assertFalse($auth->isUserAuthenticated(), 'Após cleanUserSession, não deve estar autenticado');
        $this->assertNull($auth->getTokenEntity(), 'Após cleanUserSession, tokenEntity deve ser null');
    }

    function testTouchUpdatesLastUsedAtOnAuthentication()
    {
        $user = $this->userDirector->createUser();
        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user);

        $this->assertNull($result['entity']->lastUsedAt);

        new PATAuthProvider(['token' => $result['plainText']]);

        $refreshed = $this->app->em->find(PersonalAccessToken::class, $result['entity']->id);
        $this->assertNotNull($refreshed->lastUsedAt, 'lastUsedAt deve ser atualizado após autenticação');
    }
}
