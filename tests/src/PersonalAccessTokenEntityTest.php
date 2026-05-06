<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\GuestUser;
use PersonalAccessToken\Entities\PersonalAccessToken;
use Tests\Traits\Faker;
use Tests\Traits\PersonalAccessTokenDirector;
use Tests\Traits\UserDirector;

class PersonalAccessTokenEntityTest extends Abstract\TestCase
{
    use UserDirector,
        PersonalAccessTokenDirector,
        Faker;

    function testCreateTokenGeneratesPrefixedToken()
    {
        $plainText = PersonalAccessToken::generateToken();

        $this->assertStringStartsWith(PersonalAccessToken::TOKEN_PREFIX, $plainText, 'Token deve ter o prefixo mc_pat_');
        $this->assertGreaterThan(60, strlen($plainText), 'Token deve ter comprimento adequado');
    }

    function testCreateTokenProducesUniqueTokens()
    {
        $token1 = PersonalAccessToken::generateToken();
        $token2 = PersonalAccessToken::generateToken();

        $this->assertNotEquals($token1, $token2, 'Cada token gerado deve ser único');
    }

    function testCreateTokenSetsHashAndPrefix()
    {
        $user = $this->userDirector->createUser();
        $this->app->disableAccessControl();

        $entity = new PersonalAccessToken();
        $entity->user = $user;
        $entity->name = 'Test Token';
        $entity->permissions = ['*'];
        $plainText = $entity->createToken();

        $this->assertNotEmpty($entity->tokenHash, 'Hash deve ser preenchido após createToken()');
        $this->assertNotEmpty($entity->tokenPrefix, 'Prefixo deve ser preenchido após createToken()');
        $this->assertStringStartsWith(PersonalAccessToken::TOKEN_PREFIX, $entity->tokenPrefix, 'Prefixo deve começar com mc_pat_');
        $this->assertEquals($plainText, $entity->getPlainTextToken(), 'getPlainTextToken deve retornar o texto plano');

        $this->app->enableAccessControl();
    }

    function testVerifyTokenReturnsEntityForValidToken()
    {
        $user = $this->userDirector->createUser();
        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user);

        $entity = PersonalAccessToken::verifyToken($result['plainText']);

        $this->assertNotNull($entity, 'verifyToken deve retornar a entidade para token válido');
        $this->assertEquals($result['entity']->id, $entity->id, 'verifyToken deve retornar o token correto');
    }

    function testVerifyTokenReturnsNullForInvalidToken()
    {
        $entity = PersonalAccessToken::verifyToken('mc_pat_invalidtoken123456');

        $this->assertNull($entity, 'verifyToken deve retornar null para token inexistente');
    }

    function testVerifyTokenReturnsNullForWrongPrefix()
    {
        $entity = PersonalAccessToken::verifyToken('wrong_prefix_sometoken');

        $this->assertNull($entity, 'verifyToken deve retornar null sem o prefixo correto');
    }

    function testVerifyTokenReturnsNullForDeletedToken()
    {
        $user = $this->userDirector->createUser();
        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user);

        $this->app->disableAccessControl();
        $result['entity']->delete(true);
        $this->app->enableAccessControl();

        $entity = PersonalAccessToken::verifyToken($result['plainText']);

        $this->assertNull($entity, 'verifyToken deve retornar null para token revogado (delete)');
    }

    function testVerifyTokenReturnsNullForExpiredToken()
    {
        $user = $this->userDirector->createUser();
        $past = new \DateTime('-1 day');

        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user, ['*'], $past);

        $entity = PersonalAccessToken::verifyToken($result['plainText']);

        $this->assertNull($entity, 'verifyToken deve retornar null para token expirado');
    }

    function testIsExpiredReturnsFalseWhenNoExpiry()
    {
        $user = $this->userDirector->createUser();
        $token = $this->personalAccessTokenDirector->createToken($user);

        $this->assertFalse($token->isExpired(), 'Token sem data de expiração não deve estar expirado');
    }

    function testIsExpiredReturnsFalseForFutureExpiry()
    {
        $user = $this->userDirector->createUser();
        $future = new \DateTime('+30 days');
        $token = $this->personalAccessTokenDirector->createToken($user, ['*'], $future);

        $this->assertFalse($token->isExpired(), 'Token com data futura não deve estar expirado');
    }

    function testIsExpiredReturnsTrueForPastExpiry()
    {
        $user = $this->userDirector->createUser();
        $past = new \DateTime('-1 day');
        $token = $this->personalAccessTokenDirector->createToken($user, ['*'], $past);

        $this->assertTrue($token->isExpired(), 'Token com data passada deve estar expirado');
    }

    function testHasPermission()
    {
        $user = $this->userDirector->createUser();
        $token = $this->personalAccessTokenDirector->createToken($user, ['agent.view', 'agent.modify']);

        $this->assertTrue($token->hasPermission('agent.view'));
        $this->assertTrue($token->hasPermission('agent.modify'));
        $this->assertFalse($token->hasPermission('space.view'));
    }

    function testGetTokenMask()
    {
        $user = $this->userDirector->createUser();
        $result = $this->personalAccessTokenDirector->createTokenWithPlainText($user);

        $mask = $result['entity']->getTokenMask();

        $this->assertStringStartsWith(PersonalAccessToken::TOKEN_PREFIX, $mask, 'Mascara deve começar com o prefixo');
        $this->assertStringContainsString('*', $mask, 'Mascara deve conter asteriscos');
    }

    function testTouchUpdatesLastUsedAt()
    {
        $user = $this->userDirector->createUser();
        $token = $this->personalAccessTokenDirector->createToken($user);

        $this->assertNull($token->lastUsedAt, 'lastUsedAt deve ser null antes de touch()');

        $token->touch();

        $this->assertNotNull($token->lastUsedAt, 'lastUsedAt deve ser preenchido após touch()');
    }

    function testCanUserCreateAllowsOwnerOnly()
    {
        $user = $this->userDirector->createUser();
        $otherUser = $this->userDirector->createUser();
        $token = $this->personalAccessTokenDirector->createToken($user);

        $this->app->enableAccessControl();
        $this->login($user);

        $this->assertTrue($token->canUser('create', $user), 'Dono pode criar');

        $this->login($otherUser);
        $this->assertFalse($token->canUser('create', $otherUser), 'Outro usuário não pode criar');
    }

    function testCanUserViewAllowsOwnerOnly()
    {
        $user = $this->userDirector->createUser();
        $otherUser = $this->userDirector->createUser();
        $token = $this->personalAccessTokenDirector->createToken($user);

        $this->app->enableAccessControl();
        $this->login($user);

        $this->assertTrue($token->canUser('view', $user), 'Dono pode ver');

        $this->login($otherUser);
        $this->assertFalse($token->canUser('view', $otherUser), 'Outro usuário não pode ver');
    }

    function testCanUserModifyAllowsOwnerOnly()
    {
        $user = $this->userDirector->createUser();
        $otherUser = $this->userDirector->createUser();
        $token = $this->personalAccessTokenDirector->createToken($user);

        $this->app->enableAccessControl();
        $this->login($user);

        $this->assertTrue($token->canUser('modify', $user), 'Dono pode modificar');

        $this->login($otherUser);
        $this->assertFalse($token->canUser('modify', $otherUser), 'Outro usuário não pode modificar');
    }

    function testCanUserRemoveAllowsOwnerOnly()
    {
        $user = $this->userDirector->createUser();
        $otherUser = $this->userDirector->createUser();
        $token = $this->personalAccessTokenDirector->createToken($user);

        $this->app->enableAccessControl();
        $this->login($user);

        $this->assertTrue($token->canUser('remove', $user), 'Dono pode remover');

        $this->login($otherUser);
        $this->assertFalse($token->canUser('remove', $otherUser), 'Outro usuário não pode remover');
    }

    function testCanUserDisallowsGuest()
    {
        $user = $this->userDirector->createUser();
        $token = $this->personalAccessTokenDirector->createToken($user);

        $this->app->enableAccessControl();
        $this->logout();

        $guest = GuestUser::i();

        $this->assertFalse($token->canUser('create', $guest), 'Guest não pode criar');
        $this->assertFalse($token->canUser('view', $guest), 'Guest não pode ver');
        $this->assertFalse($token->canUser('modify', $guest), 'Guest não pode modificar');
        $this->assertFalse($token->canUser('remove', $guest), 'Guest não pode remover');
    }

    function testGetControllerIdReturnsCorrectValue()
    {
        $this->assertEquals('personal-access-token', PersonalAccessToken::getControllerId());
    }

    function testDeleteChangesStatus()
    {
        $user = $this->userDirector->createUser();
        $token = $this->personalAccessTokenDirector->createToken($user);

        $this->assertEquals(PersonalAccessToken::STATUS_ENABLED, $token->status);

        $this->app->disableAccessControl();
        $token->delete(true);

        $this->assertLessThan(0, $token->status, 'Status deve ser negativo após delete');

        $this->app->enableAccessControl();
    }
}
