<?php

namespace Tests;

use MapasCulturais\App;
use Tests\Traits\LocalAuthUser;

/**
 * Metadata sensível do login local com private => true no
 * registro core-owned (cenário PÓS-MIGRAÇÃO: no ambiente de teste o plugin MLA
 * jamais carrega, logo o registro do módulo é o efetivo — exatamente o
 *
 * Chaves sensíveis (7 — as mesmas do plugin, Plugin.php:57-63):
 *   localAuthenticationPassword, recover_token, recover_token_time,
 *   accountIsActive, tokenVerifyAccount, loginAttemp, timeBlockedloginAttemp
 */
class LocalAuthMetadataPrivacyTest extends Abstract\TestCase
{
    use LocalAuthUser;

    private const SENSITIVE_KEYS = [
        'localAuthenticationPassword',
        'recover_token',
        'recover_token_time',
        'accountIsActive',
        'tokenVerifyAccount',
        'loginAttemp',
        'timeBlockedloginAttemp',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->guardLocalAuthModule();
    }

    public function testSensitiveMetadataNotExposedInUserSerialization(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'privado@test.mapas');
        $this->setRecoverToken($user, 'tok-priv-abcdef012345678', time());
        $this->setPrivateMetadata($user, 'tokenVerifyAccount', 'tok-verify-priv-1234');
        $this->setPrivateMetadata($user, 'loginAttemp', '3');

        // loga o próprio usuário antes de serializar: o canUserDeleteAccount do core
        // (pré-existente) lança TypeError com GuestUser no ->user global —
        // bug do core reportado; o objetivo AQUI é a privacidade da metadata
        $this->login($user);
        $serialized = json_encode($user->jsonSerialize());

        foreach (self::SENSITIVE_KEYS as $key) {
            $this->assertStringNotContainsString(
                $key,
                (string) $serialized,
                "metadata sensível '{$key}' não pode vazar na serialização do usuário (registro core private)"
            );
        }
        $this->assertStringNotContainsString('S3nh@Forte', (string) $serialized, 'o HASH tampouco (nem a senha, obviamente)');
    }

    public function testSensitiveMetadataNotExposedViaApiQuerySelect(): void
    {
        $user = $this->createLocalUser('S3nh@Forte', 'apiq@test.mapas');

        $query = new \MapasCulturais\ApiQuery('MapasCulturais\Entities\User', [
            'id' => 'EQ(' . $user->id . ')',
            '@select' => 'localAuthenticationPassword,recover_token,loginAttemp',
        ]);
        $result = $query->getFindResult();

        $flat = json_encode($result);
        $this->assertStringNotContainsString('localAuthenticationPassword', (string) $flat, 'ApiQuery não entrega metadata private por @select');
    }
}
