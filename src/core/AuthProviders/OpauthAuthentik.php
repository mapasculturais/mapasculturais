<?php
namespace MapasCulturais\AuthProviders;
use MapasCulturais\App;
use MapasCulturais\Entities;

/**
 * Provedor de autenticação Opauth para Authentik
 * 
 * Implementa autenticação via Opauth para o provedor Authentik
 * 
 * @package MapasCulturais\AuthProviders
 */
class OpauthAuthentik extends \MapasCulturais\AuthProvider{
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
     * Configura as rotas e hooks necessários para autenticação via Authentik
     * 
     * @return void
     */
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
            'change_password_url' => null
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
        
        if($config['logout_url'] && php_sapi_name() != "cli"){
            $app->hook('auth.logout:after', function() use($app, $config){
                $app->redirect($config['logout_url']);
            });
        }

        // Implementa botão para alterar a senha no paineld e usuario
        $app->hook('template(panel.<<my-account|user-detail>>.user-mail):end ', function () use ($app) {
            /** @var \MapasCulturais\Theme $this */
            if (isset($app->config['auth.config']) && isset($app->config['auth.config']['change_password_url']) && $app->config['auth.config']['change_password_url']) {
                $this->part('change_password_other_providers', [
                    'change_password_url' => $app->config['auth.config']['change_password_url']
                ]);
            }
        });
        
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
     * Retorna a resposta de autenticação do Opauth ou null se o usuário não tentou autenticar
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
                if(isset($response['error'])) {
                    unset($_SESSION['opauth']);
                    return null;
                }
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
     * Interrompe a execução com código e mensagem de erro
     * 
     * @param int $code Código de erro
     * @param string $msg Mensagem de erro
     * @return void
     */
    protected function halt($code, $msg) {
        die($msg);
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
            $auth_provider = $app->getRegisteredAuthProviderId('authentik');

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
