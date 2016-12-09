<?php
namespace MapasCulturais\AuthProviders;
use MapasCulturais\App;
use MapasCulturais\Entities;


class OpauthMultipleLocal extends \MapasCulturais\AuthProvider{
    protected $opauth;
    
    var $login_error        = true;
    var $login_error_msg    = 'bla';
    var $register_error     = false;
    var $register_error_msg = false;
    var $triedEmail = '';
    var $triedName = '';
    
    protected function _init() {

        $app = App::i();

        $config = array_merge([
            'timeout' => '24 hours',
            'salt' => 'LT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECU',

            'client_secret' => '',
            'cliente_id' => '',
            'path' => preg_replace('#^https?\:\/\/[^\/]*(/.*)#', '$1', $app->createUrl('auth'))
        ], $this->_config);
        $opauth_config = [
            'strategy_dir' => PROTECTED_PATH . '/vendor/opauth/',
            'Strategy' => $config['strategies'],
            'security_salt' => $config['salt'],
            'security_timeout' => $config['timeout'],
            'path' => $config['path'],
            'callback_url' => $app->createUrl('auth','response')
        ];

//        die(var_dump($opauth_config));
        $opauth = new \Opauth($opauth_config, false );
        $this->opauth = $opauth;


        // add actions to auth controller
        $app->hook('GET(auth.index)', function () use($app){
            $app->auth->renderForm($this);
        });

        $providers = implode('|', array_keys($config['strategies']));


        $app->hook("<<GET|POST>>(auth.<<{$providers}>>)", function () use($opauth, $config){
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
        
        
        $app->hook('POST(auth.register)', function () use($app){
            
            if ($app->auth->validateRegisterFields()) {
            
                // Para simplificar, montaremos uma resposta no padrão Oauth
                $response = [
                    'auth' => [
                        'provider' => 'local',
                        'uid' => $app->request->post('email'),
                        'info' => [
                            'email' => $app->request->post('email'),
                            'name' => $app->request->post('name'),
                        ]
                    ]
                ];
                
                $user = $app->auth->createUser($response);
                
                // save user password
                $user->localAuthenticationPassword = $app->auth->hashPassword($app->request->post('password'));
                var_dump($user->localAuthenticationPassword);
                var_dump($user->email);
                var_dump($user->id);
                
                //$user->save(true);
                //$user->flush();
                
                $profile = $user->profile;

                $this->_setAuthenticatedUser($user);
                //App::i()->applyHook('auth.successful');
                var_dump($user->id);
                //$app->redirect($profile->editUrl);
                
            
            } else {
                $app->auth->renderForm($this);
            }

        });
        
        
    }
    
    function validateRegisterFields() {
        return true;
    }
    
    function hashPassword($pass) {
        return md5($pass);
    }
    
    function renderForm($theme) {
        $app = App::i();
        $theme->render('multiple-local', [
            'register_form_action' => $app->createUrl('auth', 'register'),
            'login_form_action' => $app->createUrl('auth', 'login'),
            'login_error'        => $app->auth->login_error,
            'login_error_msg'    => $app->auth->login_error_msg,   
            'register_error'     => $app->auth->register_error,    
            'register_error_msg' => $app->auth->register_error_msg,
            'triedEmail' => $app->auth->triedEmail,
            'triedName' => $app->auth->triedName,
        ]);
    }
    
    public function _cleanUserSession() {
        unset($_SESSION['opauth']);
    }
    public function _requireAuthentication() {
        $app = App::i();
        if($app->request->isAjax()){
            $app->halt(401, $app->txt('This action requires authentication'));
        }else{
            $this->_setRedirectPath($app->request->getPathInfo());
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
        $app->log->debug("=======================================\n". __METHOD__. print_r($response,true) . "\n=================");

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
        
        if (is_object($this->_authenticatedUser)) {
            $app->log->debug('================' . var_dump($this->_authenticatedUser));
            return $this->_authenticatedUser;
        }
        
        $user = null;
        if($this->_validateResponse()){
            $app = App::i();
            $response = $this->_getResponse();

            $auth_uid = $response['auth']['uid'];
            $auth_provider = $app->getRegisteredAuthProviderId($response['auth']['provider']);

            $user = $app->repo('User')->findOneBy(['email' => $response['auth']['info']['email']]);

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

                $profile = $user->profile;
                $this->_setRedirectPath($profile->editUrl);
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

    protected function _createUser($response) {
        $app = App::i();

        $app->disableAccessControl();

        // cria o usuário
        $user = new Entities\User;
         $user->authProvider = $response['auth']['provider'];
        $user->authUid = $response['auth']['uid'];
        $user->email = $response['auth']['info']['email'];
        $app->em->persist($user);

        // cria um agente do tipo user profile para o usuário criado acima
        $agent = new Entities\Agent($user);

        if(isset($response['auth']['info']['name'])){
            $agent->name = $response['auth']['info']['name'];
        }elseif(isset($response['auth']['info']['first_name']) && isset($response['auth']['info']['last_name'])){
            $agent->name = $response['auth']['info']['first_name'] . ' ' . $response['auth']['info']['last_name'];
        }else{
            $agent->name = '';
        }

        $agent->emailPrivado = $user->email;

        $app->em->persist($agent);
        $app->em->flush();

        $user->profile = $agent;
        $user->save(true);

        $app->enableAccessControl();

        $this->_setRedirectPath($agent->editUrl);

        return $user;
    }
}
