<?php
namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;
use MapasCulturais\Auth\OAuth2ClientHelper;
use MapasCulturais\Entities;
use MapasCulturais\i;

/**
 * Provedor de autenticação OAuth2 (Login Cidadão) — motor league/oauth2-client.
 *
 * Login Cidadão é OAuth2 puro
 * (sem ID token no fluxo legado). PKCE 'off' por padrão com WARNING em produção
 * (condição de saída: flip para 'on' quando o IdP confirmar suporte).
 * Logout federado via logout_url?next= com next construído
 * exclusivamente server-side a partir do BASE_URL.
 */
class OpauthLoginCidadao extends \MapasCulturais\AuthProvider{
    /**
     * Helper do motor OAuth2 (interno do core)
     * @var OAuth2ClientHelper
     */
    protected OAuth2ClientHelper $oauth;

    /**
     * URL do primeiro login
     * @var string|null
     */
    protected $_firstLloginUrl = null;

    /**
     * Inicializa o provedor de autenticação
     *
     * @return void
     */
    protected function _init() {
        $app = App::i();

        $url = $app->createUrl('auth');
        $config = array_merge([
            'timeout' => '24 hours',           // legado Opauth: aceito no config, ignorado na segurança de fluxo
            'salt' => '',                      // legado Opauth: sem uso no motor novo

            'client_secret' => '',
            'client_id' => '',
            'path' => preg_replace('#^https?\:\/\/[^\/]*(/.*)#', '$1', $url),

            'pkce' => 'off',                   // R-R8: LC legado sem PKCE; 'off' + WARNING em produção; sem downgrade silencioso
            'state_ttl' => 600,
            'scope' => env('AUTH_LOGIN_CIDADAO_SCOPE', 'openid profile email'),
            'urlAuthorize' => env('AUTH_LOGIN_CIDADAO_AUTH_ENDPOINT', ''),
            'urlAccessToken' => env('AUTH_LOGIN_CIDADAO_TOKEN_ENDPOINT', ''),
            'urlResourceOwnerDetails' => env('AUTH_LOGIN_CIDADAO_USERINFO_ENDPOINT', ''),
        ], $this->_config);

        // typo histórico 'cliente_id' (chave jamais funcional) é corrigido por merge
        if (isset($this->_config['cliente_id']) && !isset($this->_config['client_id'])) {
            $config['client_id'] = $this->_config['cliente_id'];
        }

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
                $config['client_id'] = $login_cidaddao__id;
                $config['client_secret'] = $login_cidaddao__secret;
            }
        }

        // SaaS -- END

        if(isset($config['onCreateRedirectUrl'])){
            $this->onCreateRedirectUrl = $config['onCreateRedirectUrl'];
        }

        $this->oauth = new OAuth2ClientHelper('logincidadao', $config);

        $provider = $this->oauth;

        // add actions to auth controller
        // Com login local ATIVO (módulo LocalAuth) e sem MLA, o módulo renderiza
        // a página combinada em hook de prioridade anterior (-100); este redirect
        // ao IdP só roda quando o local está desligado.
        $app->hook('GET(auth.index)', function () use($app){
            if (\LocalAuth\Module::isEnabled() && !\LocalAuth\Module::multipleLocalAuthActive()) {
                return; // o módulo LocalAuth já respondeu (hook -100)
            }
            $app->redirect($this->createUrl('logincidadao'));
        });

        $app->hook('<<GET|POST>>(auth.logincidadao)', function () use($app, $provider){
            $transaction = $provider->beginAuthorization();
            $app->redirect($provider->buildAuthorizationUrl($transaction));
        });

        $app->hook('GET(auth.response)', function () use($app){
            $app->auth->processResponse();
            if($app->auth->isUserAuthenticated()){
                $app->redirect ($app->auth->getRedirectPath());
            }else{
                $app->redirect ($this->createUrl(''));
            }
        });

        if(isset($config['logout_url']) && $config['logout_url']){
            $app->hook('auth.logout:after', function() use($app, $config){
                // 'next' construído exclusivamente server-side a partir do
                // BASE_URL — nunca de input do usuário (endurece o comportamento legado).
                $app->redirect($config['logout_url'] . '?next=' . urlencode($app->getBaseUrl()));
            });
        }
    }

    /**
     * Limpa a sessão do usuário
     *
     * @return void
     */
    public function _cleanUserSession() {
        if (isset($this->oauth)) {
            $this->oauth->cleanSession();
        }
        unset($_SESSION['opauth']); // chave legada: limpeza de resíduos de sessões pré-migração
    }

    /**
     * Requer autenticação do usuário
     *
     * @return void
     */
    public function _requireAuthentication() {
        $app = App::i();
        if($app->request->isAjax()){
            $app->halt(401, i::__('This action requires authentication'));
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
     * Obtém o usuário autenticado a partir da sessão do driver.
     *
     * @return \MapasCulturais\Entities\User|null
     */
    public function _getAuthenticatedUser() {
        $user_id = $_SESSION['auth.oauth.user']['logincidadao'] ?? null;
        if (!$user_id) {
            return null;
        }
        return App::i()->repo('User')->find($user_id);
    }

    /**
     * Processa a resposta de autenticação do IdP e cria o usuário se não existir
     *
     * @return boolean true se a resposta for válida ou false se não for válida
     */
    public function processResponse(){
        $app = App::i();

        try {
            $callback = $this->oauth->validateCallback();
            if (!$callback['ok']) {
                $this->_setAuthenticatedUser();
                $app->applyHook('auth.failed');
                return false;
            }

            $providerLeague = $this->oauth->buildProvider();
            $token = $this->oauth->exchangeCode($providerLeague, $callback['code'], $callback['code_verifier']);

            $userinfo = $providerLeague->getResourceOwner($token)->toArray();

            // Compat de identidade com a strategy legada (fork opauth/google): o uid é o
            // campo 'id' do userinfo do Login Cidadão; 'sub' é o equivalente OIDC.
            $auth_uid = (string) ($userinfo['id'] ?? $userinfo['sub'] ?? '');
            if ($auth_uid === '') {
                throw new \RuntimeException('auth_failed');
            }

            $auth_provider = $app->getRegisteredAuthProviderId('logincidadao');
            $user = $app->repo('User')->getByAuth($auth_provider, $auth_uid);

            if (!$user) {
                $user = $this->createUser([
                    'uid' => $auth_uid,
                    'email' => $this->verifiedEmail($userinfo),
                    'first_name' => (string) ($userinfo['first_name'] ?? ''),
                    'surname' => (string) ($userinfo['surname'] ?? ($userinfo['family_name'] ?? '')),
                    'name' => (string) ($userinfo['name'] ?? ''),
                ]);
            }

            OAuth2ClientHelper::rotateSession();
            $_SESSION['auth.oauth.user']['logincidadao'] = $user->id; // sessão do driver
            $this->_setAuthenticatedUser($user);
            $this->oauth->audit('auth.login.success', $user, $auth_uid);
            $app->applyHook('auth.successful');
            return true;

        } catch (\Throwable $e) {
            $app->log->error('[auth] logincidadao processResponse: ' . $e->getMessage());
            $this->oauth->audit('auth.login.failed', null, 'exception');
            $this->_setAuthenticatedUser();
            $app->applyHook('auth.failed');
            return false;
        }
    }

    /**
     * E-mail somente com email_verified=true (quando o claim existe);
     * colisão de e-mail nunca auto-linka (criação usa e-mail verificado ou
     * placeholder — o repositório continua casando por (provider, uid)).
     */
    protected function verifiedEmail(array $userinfo): string {
        $email = (string) ($userinfo['email'] ?? '');
        if ($email === '') {
            return '';
        }
        $verified = $userinfo['email_verified'] ?? null;
        if ($verified === null) {
            // IdP que não fornece o claim: política conservadora com opt-in documentado
            $allow = filter_var($this->_config['allow_unverified_email'] ?? false, FILTER_VALIDATE_BOOL);
            return $allow ? $email : '';
        }
        if ($verified === true || $verified === 'true' || $verified === 1 || $verified === '1') {
            return $email;
        }
        return '';
    }

    /**
     * Cria um novo usuário a partir da resposta de autenticação
     *
     * @param array $data Resposta de autenticação normalizada
     * @return \MapasCulturais\Entities\User
     */
    protected function _createUser($data) {
        $app = App::i();

        $app->disableAccessControl();

        // cria o usuário
        $user = new Entities\User;
        $user->authProvider = 'logincidadao';
        $user->authUid = $data['uid'];
        $user->email = $data['email'] !== '' ? $data['email'] : uniqid('logincidadao-') . '@invalid.local';
        $app->em->persist($user);

        // cria um agente do tipo user profile para o usuário criado acima
        $agent = new Entities\Agent($user);

        $agent->status = 0;

        if(isset($data['first_name']) && $data['first_name'] !== '' && isset($data['surname']) && $data['surname'] !== ''){
            $agent->name = $data['first_name'] . ' ' . $data['surname'];
        }elseif(isset($data['name']) && $data['name'] !== ''){
            $agent->name = $data['name'];
        }elseif(isset($data['first_name']) && $data['first_name'] !== ''){
            $agent->name = $data['first_name'];
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
