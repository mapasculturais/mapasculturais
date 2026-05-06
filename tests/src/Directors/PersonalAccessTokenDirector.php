<?php

namespace Tests\Directors;

use MapasCulturais\App;
use MapasCulturais\Entities\User;
use PersonalAccessToken\Entities\PersonalAccessToken;
use Tests\Abstract\Director;
use Tests\Builders\PersonalAccessTokenBuilder;

class PersonalAccessTokenDirector extends Director
{
    protected PersonalAccessTokenBuilder $patBuilder;

    protected function __init()
    {
        $this->patBuilder = new PersonalAccessTokenBuilder;
    }

    function createToken(User $user, array $permissions = ['*'], ?\DateTime $expiresAt = null): PersonalAccessToken
    {
        $app = App::i();
        $app->disableAccessControl();

        $plainText = $this->patBuilder
            ->reset()
            ->setUser($user)
            ->fillRequiredProperties()
            ->setPermissions($permissions)
            ->setExpiresAt($expiresAt)
            ->generateToken();

        $token = $this->patBuilder
            ->save()
            ->getInstance();

        $token->_plainTextToken = $plainText;

        $app->enableAccessControl();
        return $token;
    }

    function createTokenWithPlainText(User $user, array $permissions = ['*'], ?\DateTime $expiresAt = null): array
    {
        $app = App::i();
        $app->disableAccessControl();

        $plainText = $this->patBuilder
            ->reset()
            ->setUser($user)
            ->fillRequiredProperties()
            ->setPermissions($permissions)
            ->setExpiresAt($expiresAt)
            ->generateToken();

        $token = $this->patBuilder
            ->save()
            ->getInstance();

        $app->enableAccessControl();
        return ['entity' => $token, 'plainText' => $plainText];
    }
}
