<?php

namespace PersonalAccessToken\AuthProviders;

use MapasCulturais\App;
use MapasCulturais\AuthProvider;
use PersonalAccessToken\Entities\PersonalAccessToken;

class PATAuthProvider extends AuthProvider
{
    protected ?PersonalAccessToken $_tokenEntity = null;

    protected function _init()
    {
        $app = App::i();
        $token = $this->_config['token'] ?? '';

        if (empty($token) || !str_starts_with($token, PersonalAccessToken::TOKEN_PREFIX)) {
            return;
        }

        $tokenEntity = PersonalAccessToken::verifyToken($token);

        if (!$tokenEntity) {
            return;
        }

        $user = $tokenEntity->user;
        if (!$user || $user->status < 1) {
            return;
        }

        $this->_tokenEntity = $tokenEntity;
        $this->setAuthenticatedUser($user);

        $tokenEntity->touch();
    }

    public function _cleanUserSession()
    {
        $this->_tokenEntity = null;
        $this->setAuthenticatedUser(null);
    }

    public function _getAuthenticatedUser()
    {
        return $this->_tokenEntity?->user;
    }

    public function getTokenEntity(): ?PersonalAccessToken
    {
        return $this->_tokenEntity;
    }

    public function getRedirectPath()
    {
        return null;
    }

    public function processResponse()
    {
    }

    protected function _createUser($data)
    {
        return null;
    }
}
