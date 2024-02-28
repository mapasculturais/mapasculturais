<?php
namespace MapasCulturais\AuthProviders;
use MapasCulturais\App;
use MapasCulturais\Entities;


class OpauthAuthentik extends \MapasCulturais\AuthProvider{
    protected $opauth;

    protected $_firstLloginUrl = null;

    protected function _init() {
        $app = App::i();
        //eval(\psy\sh());
        $url = $app->createUrl('auth');
        $config = array_merge([
            'timeout' => '24 hours',
            'salt' => 'LT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECURITY_SALT_SECU',
            'client_secret' => '',
            'client_id' => '',
            'login_url' => '',
            'path' => preg_replace('#^https?\:\/\/[^\/]*(/.*)#', '$1', $url),
            'logout_url' => $app->createUrl('site','index'),
        ], $this->_config);
        
        
         $opauth_config = [
            'strategy_dir' => PROTECTED_PATH . 'vendor/opauth/',
            'Strategy' => [
                'authentik' => $config
            ],
            'security_salt' => $config['salt'],
            'security_timeout' => $config['timeout'],
            'host' => preg_replace('#^(https?\:\/\/[^\/]*)/.*#', '$1', $url),
            'path' => $config['path'],
            'callback_url' => $app->createUrl('auth','response')
        ];
        
        $metadata = [
            'authentik__id' => ['label' => 'Authentik Client ID', 'private' => 'true'],
            'authentik__secret' => ['label' => 'Authentik Client Secret', 'private' => 'true']
        ];

        foreach($metadata as $k => $cfg){
            $def = new \MapasCulturais\Definitions\Metadata($k, $cfg);
            //$app->registerMetadata($def, 'MapasCulturais\Entities\Subsite');
        }

        $opauth = new \Opauth($opauth_config, false );
        $this->opauth = $opauth;


        // add actions to auth controller
        $app->hook('GET(auth.index)', function () use($app){
            $app->redirect($this->createUrl('authentik'));
            //$app->redirect('https://dev-id.florestaativista.org/application/o/authorize/');
        });
        $app->hook('<<GET|POST>>(auth.authentik)', function () use($opauth, $config){
            //$_POST['openid_url'] = $config['login_url'];
            $opauth->run();
        });
        $app->hook('GET(auth.response)', function () use($app){
            //eval(\psy\sh());
            $app->auth->processResponse();
            if($app->auth->isUserAuthenticated()){
                $app->redirect ($app->auth->getRedirectPath());
            }else{
                $app->redirect ($this->createUrl(''));
            }
        });
        
        if($config['logout_url']){
            $app->hook('auth.logout:after', function() use($app, $config){
                $app->redirect($config['logout_url']);
            });
        }
        
    }
    public function _cleanUserSession() {
        unset($_SESSION['opauth']);
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

    protected function halt($code, $msg) {
        die($msg);
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
            //eval(\psy\sh());
            $this->halt(500, 'Opauth returns error auth response');
        } else {
            /**
            * Auth response validation
            *
            * To validate that the auth response received is unaltered, especially auth response that
            * is sent through GET or POST.
            */
            if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])) {
                $this->halt(500, 'Invalid auth response: Missing key auth response components.');
            } elseif (!$this->opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)) {
                $this->halt(500, "Invalid auth response: {$reason}");
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
            $auth_provider = $app->getRegisteredAuthProviderId('authentik');

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

                $profile = $user->profile;
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
        $user->email = $response['auth']['raw']['email'];
        $app->em->persist($user);

        // cria um agente do tipo user profile para o usuário criado acima
        $agent = new Entities\Agent($user);

        $agent->status = 1;
        
        if(isset($response['auth']['raw']['name'])){
            $agent->name = $response['auth']['raw']['name'];
        }else{
            $agent->name = '';
        }

        $agent->emailPrivado = $user->email;
        $agent->save(true);
        $app->em->persist($agent);
        $app->em->flush();

        $user->profile = $agent;
        $user->save(true);

        $app->enableAccessControl();

        return $user;
    }
}
