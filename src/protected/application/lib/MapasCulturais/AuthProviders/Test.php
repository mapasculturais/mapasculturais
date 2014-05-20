<?php
namespace MapasCulturais\AuthProviders;

class Test extends \MapasCulturais\AuthProvider{
    protected $_user = null;

    protected function _init() {}

    public function _cleanUserSession() {
        $this->_user = null;
    }

    public function _requireAuthentication() {
        $app = \MapasCulturais\App::i();
        $app->halt(401, $app->txt('This action requires authentication.'));
    }

    /**
     * Defines the URL to redirect after authentication
     * @param string $redirect_path
     */
    protected function _setRedirectPath($redirect_path){ }

    /**
     * Returns the URL to redirect after authentication
     * @return string
     */
    public function getRedirectPath(){
        return '';
    }


    public function _getAuthenticatedUser() {
        return $this->_user;
    }

    public function setAuthenticatedUser(\MapasCulturais\Entities\User $user){
        $this->_setAuthenticatedUser($user);
    }
}
