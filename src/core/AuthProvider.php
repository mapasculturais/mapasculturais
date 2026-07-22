<?php
namespace MapasCulturais;

use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\User;
use MapasCulturais\Exceptions\PermissionDenied;

/**
 * Classe abstrata base para provedores de autenticação
 * 
 * @property Entities\User|null $authenticatedUser O usuário autenticado ou null
 * 
 * @package MapasCulturais
 */
abstract class AuthProvider {
    use Traits\MagicCallers,
        Traits\MagicGetter,
        Traits\MagicSetter;

    /**
     * Configurações do provedor de autenticação
     * @var array
     */
    protected $_config = [];

    /**
     * Instância do usuário autenticado
     * @var Entities\User|null
     */
    private $_authenticatedUser = null;

    /**
     * Instância do usuário convidado
     * @var GuestUser|null
     */
    private $_guestUser = null;

    /**
     * Construtor da classe
     * 
     * @param array $config
     */
    function __construct(array $config = []) {
        $this->_config = $config;
        $this->_init();
        $this->_authenticatedUser = $this->_getAuthenticatedUser();
        $this->_guestUser = new GuestUser();
        $app = App::i();

        $app->hook('auth.successful', function() use($app){
            $user = $app->user;

            $preventOverhead = (bool) ($user->metadata['preventOverhead'] ?? false);
            if (!$preventOverhead) {
                $user->getEntitiesNotifications($app);
            }

            $user->lastLoginTimestamp = new \DateTime;
            $user->save(true);
        });
    }

    /**
     * Inicializa o provedor (implementação dependente do driver)
     */
    abstract protected function _init();

    /**
     * Limpa a sessão do usuário (implementação dependente do driver)
     */
    abstract function _cleanUserSession();

    /**
     * Cria um novo usuário (implementação dependente do driver)
     * 
     * @param array $data
     * @return \MapasCulturais\Entities\User
     */
    abstract protected function _createUser($data);

    /**
     * Cria um novo usuário e executa ações pós-criação (cache, e-mail de boas-vindas)
     * 
     * @param array $data
     * @return \MapasCulturais\Entities\User
     */
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

    /**
     * Realiza o logout do usuário
     * 
     * @return void
     */
    final function logout(){
        App::i()->applyHookBoundTo($this, 'auth.logout:before', [$this->_authenticatedUser]);

        $this->_authenticatedUser = null;
        $this->_cleanUserSession();

        App::i()->applyHookBoundTo($this, 'auth.logout:after');
    }

    /**
     * Exige autenticação para prosseguir
     * 
     * @param string|null $redirect_url URL para redirecionar após o login
     * @return void
     */
    final function requireAuthentication($redirect_url = null){
        $app = App::i();
        $app->applyHookBoundTo($this, 'auth.requireAuthentication');
        $this->_setRedirectPath($redirect_url ? $redirect_url : $app->request->psr7request->getUri()->getPath());
        $this->_requireAuthentication();
    }

    /**
     * Executa a ação de exigir autenticação (redirecionamento ou erro JSON)
     * 
     * @return void
     */
    protected function _requireAuthentication() {
        $app = App::i();
        if($app->request->isAjax() || $app->request->getHeaderLine('Content-Type') === 'application/json'){
            $app->view->controller->errorJson(\MapasCulturais\i::__('Esta ação requer autenticação'), 401);
        }else{
            $app->redirect($app->controller('auth')->createUrl(''), 302);
        }
    }

    /**
     * Define a URL para redirecionar após a autenticação
     * @param string $redirect_path
     */
    public function setRedirectPath(string $redirect_path) {
        $this->_setRedirectPath($redirect_path);
    }

    /**
     * Define a URL para redirecionar após a autenticação (interno)
     * @param string $redirect_path
     */
    protected function _setRedirectPath(string $redirect_path) {
        $_SESSION['mapasculturais.auth.redirect_path'] = $redirect_path;
    }

    /**
     * Retorna a URL de redirecionamento pós-autenticação
     * 
     * @return string
     */
    protected function getRedirectPath() {
        $app = App::i();
        
        $redirect = $_SESSION['mapasculturais.auth.redirect_path'] ?? $app->createUrl('panel', 'index');

        $app->applyHookBoundTo($this, 'auth.redirectUrl', [&$redirect]);
        
        return $redirect;
    }

    /**
     * Define o usuário autenticado e dispara o hook de login
     * 
     * @param Entities\User|null $user
     * @return void
     */
    protected final function _setAuthenticatedUser(Entities\User|null $user = null){
        $this->_authenticatedUser = $user;
        App::i()->applyHookBoundTo($this, 'auth.login', [$user]);
    }

    /**
     * Define o usuário autenticado.
     *
     * @param Entities\User|null $user O usuário autenticado, ou null se não houver usuário autenticado.
     */
    protected function setAuthenticatedUser(Entities\User|null $user = null){
        $this->_authenticatedUser = $user;
    }

    /**
     * Obtém o usuário autenticado da sessão ou cookie (implementação dependente do driver)
     */
    abstract function _getAuthenticatedUser();

    /**
     * Retorna o usuário autenticado ou a instância de GuestUser
     * 
     * @return Entities\User|GuestUser
     * @throws PermissionDenied caso o usuário esteja inativo
     */
    final function getAuthenticatedUser(){
        $user = $this->_authenticatedUser;
        
        if (!$user instanceof User) {
            return $this->_guestUser;
        } 

        if ($user->status < 1) {
            $this->logout();
            throw new PermissionDenied($user, message: i::__('Usuário inativo'));
        }

        return $user;

    }

    /**
     * Verifica se o usuário está autenticado
     * 
     * @return bool
     */
    final function isUserAuthenticated(){
        return !is_null($this->_authenticatedUser);
    }


    /**
     * Define os cookies de autenticação
     * 
     * @return void
     */
    function setCookies(){
        $user_id = $this->isUserAuthenticated() ? $this->getAuthenticatedUser()->id : 0;
        $user_is_adm = $this->getAuthenticatedUser()->is('admin');
        if (php_sapi_name() != "cli") {
            setcookie('mapasculturais.uid', $user_id, 0, '/');
            setcookie('mapasculturais.adm', $user_is_adm, 0, '/');
        }
    }
}
