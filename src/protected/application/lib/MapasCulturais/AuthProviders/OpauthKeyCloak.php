<?php
namespace MapasCulturais\AuthProviders;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\AuthProviders\JWT;

class OpauthKeyCloak extends \MapasCulturais\AuthProvider{
    protected $opauth;

    protected $_firstLloginUrl = null;

    protected $baseUrl = '';

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
        
        preg_match('#(https?://[^/]+/)#',$config['auth_endpoint'], $matches);
        $this->baseUrl = $matches[0];

         $opauth_config = [
            'strategy_dir' => PROTECTED_PATH . '/vendor/opauth/',
            'Strategy' => [
                'keycloak' => $config
            ],
            'security_salt' => $config['salt'],
            'security_timeout' => $config['timeout'],
            'host' => preg_replace('#^(https?\:\/\/[^\/]*)/.*#', '$1', $url),
            'path' => $config['path'],
            'callback_url' => $app->createUrl('auth','response')
        ];
        
        $metadata = [
            'key_cloak__id' => ['label' => 'KeyCloak Client ID', 'private' => 'true'],
            'key_cloak__secret' => ['label' => 'Key Cloak Client Secret', 'private' => 'true']
        ];

        foreach($metadata as $k => $cfg){
            $def = new \MapasCulturais\Definitions\Metadata($k, $cfg);
            $app->registerMetadata($def, 'MapasCulturais\Entities\Subsite');
        }
        
        if($subsite = $app->getCurrentSubsite()){
            $key_cloak__id = $subsite->getMetadata('key_cloak__id');
            $key_cloak__secret = $subsite->getMetadata('key_cloak__secret');
            
            if($key_cloak__id && $key_cloak__secret){
                $opauth_config['Strategy']['keycloak']['client_id'] = $key_cloak__id;
                $opauth_config['Strategy']['keycloak']['client_secret'] = $key_cloak__secret;
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
            $app->redirect($this->createUrl('keycloak'));
        });
        $app->hook('<<GET|POST>>(auth.keycloak)', function () use($opauth, $config){
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
                $app->redirect($config['logout_url'] . '?redirect_uri=' . $app->baseUrl);
            });
        }
        
    }
    public function _cleanUserSession() {
        unset($_SESSION['opauth']);
    }
    
    private function getUriHttpReferer()
    {
        $app = App::i();

        if (isset($_SERVER['HTTP_REFERER'])) {
            $caminho = $app->request()->cookies('mapasculturais_user_nav_url');
            if (($_SERVER['HTTP_REFERER']==$app->createUrl('site', 'search')) && isset($caminho)) {
                $path = $caminho;
            } else {
                $path = $_SERVER['HTTP_REFERER'];
            }
        } else {
            $path = $app->auth->getRedirectPath();
        }
       
        return $path;
    }

    public function _requireAuthentication() {
        $app = App::i();
        if($app->request->isAjax()){
            $app->halt(401, \MapasCulturais\i::__('This action requires authentication'));
        }else{
            $_SESSION['UriHttpReferer'] = $this->getUriHttpReferer();
            $this->_setRedirectPath($this->getUriHttpReferer());
            $app->redirect($app->controller('auth')->createUrl(''), 401);
        }
    }
    /**
     * Defines the URL to redirect after authentication
     * @param string $redirect_path
     */
    protected function _setRedirectPath($redirect_path) {
        parent::_setRedirectPath($redirect_path);
    }
    /**
     * Returns the URL to redirect after authentication
     * @return string
     */
    public function getRedirectPath(){
        $path = key_exists('mapasculturais.auth.redirect_path', $_SESSION) ?
                    $_SESSION['mapasculturais.auth.redirect_path'] : App::i()->createUrl('site','');
        unset($_SESSION['mapasculturais.auth.redirect_path']);
        return $path;
    }
    /**
     * Returns the Opauth authentication response or null if the user not tried to authenticate
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
     * Check if the Opauth response is valid. If it is valid, the user is authenticated.
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
    public function _getAuthenticatedUser() {
        $user = null;
        if($this->_validateResponse()){
            $app = App::i();
            $response = $this->_getResponse();
            $auth_uid = $response['auth']['uid'];
            $auth_provider = $app->getRegisteredAuthProviderId('keycloak');
            $user = $app->repo('User')->getByAuth($auth_provider, $auth_uid);
            return $user;
        }else{
            return null;
        }
    }
    /**
     * Process the Opauth authentication response and creates the user if it not exists
     * @return boolean true if the response is valid or false if the response is not valid
     */
    public function processResponse(){
        // se autenticou
        if($this->_validateResponse()){
            // e ainda não existe um usuário no sistema
            $user = $this->_getAuthenticatedUser();
            if(!$user){
                $response = $this->_getResponse();
                $user = $this->createUser($response);

                
                $this->lastRedirectPath();
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


    //Método que pega a última URL antes de criar o login ou do usuário logar no mapa da saúde
    public function lastRedirectPath(){
        $path = $this->_setRedirectPath($_SESSION['UriHttpReferer']);
        return $path;
    }

    protected function _createUser($response) {
        
    
        $app = App::i();

        $app->disableAccessControl();

        // cria o usuário
        $user = new Entities\User;
        $user->authProvider = $response['auth']['provider'];
        $user->authUid = $response['auth']['uid'];
        $user->email = $response['auth']['raw']['email'];
        
        if (!empty($response['auth']['raw']['CPF'])) {
            $user->cpf = $response['auth']['raw']['CPF'];
        }

        if (!empty($response['auth']['raw']['TELEFONE'])) {
            $user->telefone = $response['auth']['raw']['TELEFONE'];
        }
        
        $app->em->persist($user);
        // cria um agente do tipo user profile para o usuário criado acima
        $agent = new Entities\Agent($user);
        $agent->status = 1;

        if(isset($response['auth']['raw']['name']) && isset($response['auth']['raw']['surname'])){
            $agent->name = $response['auth']['raw']['name'] . ' ' . $response['auth']['raw']['surname'];
            $agent->nomeCompleto = $response['auth']['raw']['name'] . ' ' . $response['auth']['raw']['surname'];
        }else if(isset($response['auth']['raw']['name'])){
            $agent->name            = $response['auth']['raw']['name'];
            $agent->nomeCompleto    = $response['auth']['raw']['name'];
        }else{
            $agent->name        = '';
            $agent->nomeCompleto= '';
        }

        $agent->emailPrivado = $user->email;
        
        if (!empty($response['auth']['raw']['preferred_username'])) {
            $agent->documento = $response['auth']['raw']['preferred_username'];
        }

        $agent->save();
        $app->em->persist($agent);
        $app->em->flush();

        $user->profile = $agent;
        $user->save(true);

        $app->enableAccessControl();

        $this->_setRedirectPath($this->onCreateRedirectUrl ? $this->onCreateRedirectUrl : $agent->editUrl);

        return $user;
        
    }

    function getChangePasswordUrl() {
        return $this->baseUrl . 'auth/realms/saude/account/password';
    }
}