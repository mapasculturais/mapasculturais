<?php
namespace MapasCulturais\AuthProviders;
use MapasCulturais\App;
use MapasCulturais\Entities;

/**
 * Provedor de autenticação Opauth para Login Cidadão
 * 
 * Implementa autenticação via Opauth para o provedor Login Cidadão
 * 
 * @package MapasCulturais\AuthProviders
 */
class OpauthLoginCidadao extends \MapasCulturais\AuthProvider{
    /**
     * Instância do Opauth
     * @var \Opauth
     */
    protected $opauth;

    /**
     * URL do primeiro login
     * @var string|null
     */
    protected $_firstLloginUrl = null;

    /**
     * Inicializa o provedor de autenticação
     * 
     * Configura as rotas e hooks necessários para autenticação via Login Cidadão
     * 
     * @return void
     */
    protected function _init() {
        $app = App::i();
        
        $url = $app->createUrl('auth');
        $config = array_merge([
            'timeout' => '24 hours',
            'salt' => 'LT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECU',

            'client_secret' => '',
            'cliente_id' => '',
            'path' => preg_replace('#^https?\:\/\/[^\/]*(/.*)#', '$1', $url)
        ], $this->_config);
        
        
         $opauth_config = [
            'strategy_dir' => PROTECTED_PATH . '/vendor/opauth/',
            'Strategy' => [
                'logincidadao' => $config
            ],
            'security_salt' => $config['salt'],
            'security_timeout' => $config['timeout'],
            'host' => preg_replace('#^(https?\:\/\/[^\/]*)/.*#', '$1', $url),
            'path' => $config['path'],
            'callback_url' => $app->createUrl('auth','response')
        ];
        
        
        //  SaaS -- BEGIN
        $app->hook('template(subsite.<<*>>.tabs):end', function() use($app){
            if($app->user->is('saasAdmin') || $app->user->is('superSaasAdmin')) {
                $this->part('singles/subsite--login-cidadao--tab');
            }
        });
        
        $app->hook('template(subsite.<<*>>.tabs-content):end', function() use($app){
            if($app->user->is('saasAdmin') || $app->user->is('superSaasAdmin')) {
                $this->part('singles/subsite--login-cidadao--content');
            }
        });
        
        $metadata = [
            'login_cidaddao__id' => ['label' => 'Login Cidadão Client ID', 'private' => 'true'],
            'login_cidaddao__secret' => ['label' => 'Login Cidadão Client Secret', 'private' => 'true']
        ];

        foreach($metadata as $k => $cfg){
            $def = new \MapasCulturais\Definitions\Metadata($k, $cfg);
            $app->registerMetadata($def, 'MapasCulturais\Entities\Subsite');
        }
        
        if($subsite = $app->getCurrentSubsite()){

            
            $login_cidaddao__id = $subsite->getMetadata('login_cidaddao__id');
            $login_cidaddao__secret = $subsite->getMetadata('login_cidaddao__secret');
            
            if($login_cidaddao__id && $login_cidaddao__secret){
                $opauth_config['Strategy']['logincidadao']['client_id'] = $login_cidaddao__id;
                $opauth_config['Strategy']['logincidadao']['client_secret'] = $login_cidaddao__secret;
            }
        }
        
        // SaaS -- END

        if(isset($config['onCreateRedirectUrl'])){
            $this->onCreateRedirectUrl = $config['onCreateRedirectUrl'];
        }

        $opauth = new \Opauth($opauth_config, false );
        $this->opauth = $opauth;


        // add actions to auth controller
        $app->hook('GET(auth.index)', function () use($app){
            $app->redirect($this->createUrl('logincidadao'));
        });
        $app->hook('<<GET|POST>>(auth.logincidadao)', function () use($opauth, $config){
//            $_POST['openid_url'] = $config['login_url'];
            $opauth->run();
        });
        $app->hook('GET(auth.response)', function () use($app){
            $app->auth->processResponse();
            if($app->auth->isUserAuthenticated()){
                $app->redirect ($app->auth->getRedirectPath());
            }else{
                $app->redirect ($this->createUrl(''));
            }
        });
        
        if($config['logout_url']){
            $app->hook('auth.logout:after', function() use($app, $config){
                $app->redirect($config['logout_url'] . '?next=' . $app->baseUrl);
            });
        }
        
    }
    
    /**
     * Limpa a sessão do usuário
     * 
     * @return void
     */
    public function _cleanUserSession() {
        unset($_SESSION['opauth']);
    }
    
    /**
     * Requer autenticação do usuário
     * 
     * @return void
     * @throws \Exception Se a requisição for AJAX, retorna erro 401
     */
    public function _requireAuthentication() {
        $app = App::i();
        if($app->request->isAjax()){
            $app->halt(401, \MapasCulturais\i::__('This action requires authentication'));
        }else{
            $this->_setRedirectPath($app->request->getPathInfo());
            $app->redirect($app->controller('auth')->createUrl(''), 401);
        }
    }
    
    /**
     * Define a URL para redirecionamento após autenticação
     * 
     * @param string $redirect_path Caminho para redirecionamento
     * @return void
     */
    protected function _setRedirectPath($redirect_path) {
        parent::_setRedirectPath($redirect_path);
    }
    
    /**
     * Retorna a URL para redirecionamento após autenticação
     * 
     * @return string
     */
    public function getRedirectPath(){
        $path = key_exists('mapasculturais.auth.redirect_path', $_SESSION) ?
                    $_SESSION['mapasculturais.auth.redirect_path'] : App::i()->createUrl('site','');
        unset($_SESSION['mapasculturais.auth.redirect_path']);
        return $path;
    }
    
    /**
     * Retorna a resposta de autenticação do Opauth ou null se o usuário não tentou autenticar
     * 
     * @return array|null
     */
    protected function _getResponse(){
        $app = App::i();
        /**
        * Fetch auth response, based on transport configuration for callback
        */
        $response = null;
        switch($this->opauth->env['callback_transport']) {
            case 'session':
                $response = key_exists('opauth', $_SESSION) ? $_SESSION['opauth'] : null;
                break;
            case 'post':
                $response = unserialize(base64_decode( $_POST['opauth'] ));
                break;
            case 'get':
                $response = unserialize(base64_decode( $_GET['opauth'] ));
                break;
            default:
                $app->log->error('Opauth Error: Unsupported callback_transport.');
                break;
        }
        return $response;
    }
    
    /**
     * Verifica se a resposta do Opauth é válida
     * 
     * Se for válida, o usuário está autenticado
     * 
     * @return boolean
     */
    protected function _validateResponse(){
        $app = App::i();
        $reason = '';
        $response = $this->_getResponse();
        $valid = false;
        // o usuário ainda não tentou se autenticar
        if(!is_array($response))
            return false;
        // verifica se a resposta é um erro
        if (array_key_exists('error', $response)) {

            $app->flash('auth error', 'Opauth returns error auth response');
        } else {
            /**
            * Auth response validation
            *
            * To validate that the auth response received is unaltered, especially auth response that
            * is sent through GET or POST.
            */
            if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])) {
                $app->flash('auth error', 'Invalid auth response: Missing key auth response components.');
            } elseif (!$this->opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)) {
                $app->flash('auth error', "Invalid auth response: {$reason}");
            } else {
                $valid = true;
            }
        }
        return $valid;
    }
    
    /**
     * Obtém o usuário autenticado
     * 
     * @return \MapasCulturais\Entities\User|null
     */
    public function _getAuthenticatedUser() {
        $user = null;
        if($this->_validateResponse()){
            $app = App::i();
            $response = $this->_getResponse();
            $auth_uid = $response['auth']['uid'];
            $auth_provider = $app->getRegisteredAuthProviderId('logincidadao');

            $user = $app->repo('User')->getByAuth($auth_provider, $auth_uid);
            return $user;
        }else{
            return null;
        }
    }
    
    /**
     * Processa a resposta de autenticação do Opauth e cria o usuário se não existir
     * 
     * @return boolean true se a resposta for válida ou false se não for válida
     */
    public function processResponse(){
        // se autenticou
        if($this->_validateResponse()){
            // e ainda não existe um usuário no sistema
            $user = $this->_getAuthenticatedUser();
            if(!$user){
                $response = $this->_getResponse();
                $user = $this->createUser($response);

                $profile = $user->profile;
                $this->_setRedirectPath($this->onCreateRedirectUrl ? $this->onCreateRedirectUrl : $profile->editUrl);
            }
            $this->_setAuthenticatedUser($user);
            App::i()->applyHook('auth.successful');
            return true;
        } else {
            $this->_setAuthenticatedUser();
            App::i()->applyHook('auth.failed');
            return false;
        }
    }

    /**
     * Cria um novo usuário a partir da resposta de autenticação
     * 
     * @param array $response Resposta de autenticação do Opauth
     * @return \MapasCulturais\Entities\User
     */
    protected function _createUser($response) {
        $app = App::i();

        $app->disableAccessControl();

        // cria o usuário
        $user = new Entities\User;
        $user->authProvider = $response['auth']['provider'];
        $user->authUid = $response['auth']['uid'];
        $user->email = $response['auth']['raw']['email'];
        $app->em->persist($user);

        // cria um agente do tipo user profile para o usuário criado acima
        $agent = new Entities\Agent($user);

        $agent->status = 0;

        if(isset($response['auth']['raw']['first_name']) && isset($response['auth']['raw']['surname'])){
            $agent->name = $response['auth']['raw']['first_name'] . ' ' . $response['auth']['raw']['surname'];
        }else if(isset($response['auth']['raw']['first_name'])){
            $agent->name = $response['auth']['raw']['first_name'];
        }else{
            $agent->name = '';
        }

        $agent->emailPrivado = $user->email;
        $agent->save();
        $app->em->persist($agent);
        $app->em->flush();

        $user->profile = $agent;
        $user->save(true);

        $app->enableAccessControl();

        $this->_setRedirectPath($this->onCreateRedirectUrl ? $this->onCreateRedirectUrl : $agent->editUrl);

        return $user;
    }
}
