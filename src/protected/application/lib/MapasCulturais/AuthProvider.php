<?php
namespace MapasCulturais;

abstract class AuthProvider {
    use Traits\Singleton,
        Traits\MagicGetter,
        Traits\MagicSetter;

    protected $_config = array();

    private $_authenticatedUser = null;

    private $_guestUser = null;

    protected final function __construct($config = array()) {
        $this->_config = $config;
        $this->_init();
        $this->_authenticatedUser = $this->_getAuthenticatedUser();
        $this->_guestUser = new GuestUser();
    }

    abstract protected function _init();

    abstract function _cleanUserSession();

    final function logout(){
        App::i()->applyHookBoundTo($this, 'auth.logout', array($this->_authenticatedUser));

        $this->_authenticatedUser = null;
        $this->_cleanUserSession();
    }

    abstract function _requireAuthentication();

    final function requireAuthentication(){
        App::i()->applyHookBoundTo($this, 'auth.requireAuthentication');
        $this->_requireAuthentication();
    }

    protected final function _setAuthenticatedUser(Entities\User $user){
        App::i()->applyHookBoundTo($this, 'auth.login', array($user));
        $this->_authenticatedUser = $user;
    }

    abstract function _getAuthenticatedUser();

    final function getAuthenticatedUser(){
        if(is_null($this->_authenticatedUser))
            return $this->_guestUser;
        else
            return $this->_authenticatedUser;

    }

    final function isUserAuthenticated(){
        return !is_null($this->_authenticatedUser);
    }


    function setCookies(){
        $user_id = $this->isUserAuthenticated() ? $this->getAuthenticatedUser()->id : 0;
        $user_is_adm = $this->getAuthenticatedUser()->is('admin');

        setcookie('mapasculturais.uid', $user_id, 0, '/');
        setcookie('mapasculturais.adm', $user_is_adm, 0, '/');

    }
}