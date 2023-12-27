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

    function __construct(array $config = []) {
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
        $user->createPermissionsCacheForUsers([$user]);
        $user->profile->createPermissionsCacheForUsers([$user]);
        $app->applyHookBoundTo($this, 'auth.createUser:after', [$user, $data]);

        $dataValue = ['name' => $user->profile->name];
        $message = $app->renderMailerTemplate('welcome',$dataValue);

        $app->createAndSendMailMessage([
            'from' => $app->config['mailer.from'],
            'to' => $user->email,
            'subject' => $message['title'],
            'body' => $message['body']
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
        $this->_setRedirectPath($redirect_url ? $redirect_url : $app->request->psr7request->getUri()->getPath());
        $this->_requireAuthentication();
    }

    protected function _requireAuthentication() {
        $app = App::i();
        if($app->request->isAjax() || $app->request->getHeaderLine('Content-Type') === 'application/json'){
            $app->view->controller->errorJson(\MapasCulturais\i::__('Esta ação requer autenticação'), 401);
        }else{
            $app->redirect($app->controller('auth')->createUrl(''), 302);
        }
    }

    /**
     * Defines the URL to redirect after authentication
     * @param string $redirect_path
     */
    public function setRedirectPath(string $redirect_path) {
        $this->_setRedirectPath($redirect_path);
    }

    /**
     * Defines the URL to redirect after authentication
     * @param string $redirect_path
     */
    protected function _setRedirectPath(string $redirect_path) {
        $_SESSION['mapasculturais.auth.redirect_path'] = $redirect_path;
    }

    protected function getRedirectPath() {
        $app = App::i();
        
        $redirect = $_SESSION['mapasculturais.auth.redirect_path'] ?? $app->createUrl('panel', 'index');

        $app->applyHookBoundTo($this, 'auth.redirectUrl', [&$redirect]);
        
        return $redirect;
    }

    protected final function _setAuthenticatedUser(Entities\User $user = null){
        $this->_authenticatedUser = $user;
        App::i()->applyHookBoundTo($this, 'auth.login', [$user]);
    }

    abstract function _getAuthenticatedUser();

    final function getAuthenticatedUser(){
        $user = $this->_authenticatedUser;
        if (is_null($user)) {
            return $this->_guestUser;
        } else {
            if ($user->status < 1) {
                $this->logout();
                die(i::__('Usuário não está ativo'));
            } else {
                return $user;
            }
        }

    }

    final function isUserAuthenticated(){
        return !is_null($this->_authenticatedUser);
    }


    function setCookies(){
        $user_id = $this->isUserAuthenticated() ? $this->getAuthenticatedUser()->id : 0;
        $user_is_adm = $this->getAuthenticatedUser()->is('admin');
        if (php_sapi_name() != "cli") {
            setcookie('mapasculturais.uid', $user_id, 0, '/');
            setcookie('mapasculturais.adm', $user_is_adm, 0, '/');
        }
    }
}
