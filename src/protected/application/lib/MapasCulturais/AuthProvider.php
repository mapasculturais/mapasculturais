<?php
namespace MapasCulturais;

use MapasCulturais\Entities\Notification;

abstract class AuthProvider {
    use Traits\MagicCallers,
        Traits\MagicGetter,
        Traits\MagicSetter;

    protected $_config = [];

    private $_authenticatedUser = null;

    private $_guestUser = null;

    final function __construct(array $config = []) {
        $this->_config = $config;
        $this->_init();
        $this->_authenticatedUser = $this->_getAuthenticatedUser();
        $this->_guestUser = new GuestUser();
        $app = App::i();

        $app->hook('auth.successful', function() use($app){
            $user = $app->user;
            $user->getEntitiesNotifications($app);
            $user->lastLoginTimestamp = new \DateTime;
            $user->save(true);
        });
    }

    abstract protected function _init();

    abstract function _cleanUserSession();

    /**
     * @return \MapasCulturais\Entities\User
     */
    abstract protected function _createUser($data);

    final protected function createUser($data){
        $app = App::i();
        $app->applyHookBoundTo($this, 'auth.createUser:before', [$data]);
        $user = $this->_createUser($data);
        $app->applyHookBoundTo($this, 'auth.createUser:after', [$user, $data]);
        $app->createAndSendMailMessage([
            'from' => $app->config['mailer.from'],
            'to' => $user->email,
            'subject' => "Bem-vindo(a) ao Mapas Culturais",
            'body' => "Bem-vindo(a) ao Mapas Culturais {$user->profile->name}, comece a cadastrar seus agentes, espaços e eventos."
        ]);
        return $user;
    }

    final function logout(){
        App::i()->applyHookBoundTo($this, 'auth.logout:before', [$this->_authenticatedUser]);

        $this->_authenticatedUser = null;
        $this->_cleanUserSession();

        App::i()->applyHookBoundTo($this, 'auth.logout:after');
    }

    final function requireAuthentication($redirect_url = null){
        $app = App::i();
        $app->applyHookBoundTo($this, 'auth.requireAuthentication');
        $this->_setRedirectPath($redirect_url ? $redirect_url : $app->request->getPathInfo());
        $this->_requireAuthentication();
    }

    protected function _requireAuthentication() {
        $app = App::i();

        if($app->request->isAjax()){
            $app->halt(401, \MapasCulturais\i::__('This action requires authentication'));
        }else{
            $app->redirect($app->controller('auth')->createUrl(''), 401);
        }
    }


    /**
     * Defines the URL to redirect after authentication
     * @param string $redirect_path
     */
    protected function _setRedirectPath($redirect_path){
        $_SESSION['mapasculturais.auth.redirect_path'] = $redirect_path;
    }

    protected final function _setAuthenticatedUser(Entities\User $user = null){
        App::i()->applyHookBoundTo($this, 'auth.login', [$user]);
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
