<?php
namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;
use MapasCulturais\Auth\OAuth2ClientHelper;
use MapasCulturais\Entities;
use MapasCulturais\i;

/**
 * Provedor de autenticação OIDC (Authentik) — motor league/oauth2-client.
 *
 * Substitui o motor Opauth mantendo nome de classe, rotas
 * (/auth/authentik, /auth/response), hooks auth.*, posição no
 * registerAuthProvider e compatibilidade de
 * auth.config. Ver também UPGRADING.md.
 */
class OpauthAuthentik extends \MapasCulturais\AuthProvider{
    /**
     * Helper do motor OAuth2/OIDC (interno do core)
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
     * Configura as rotas e hooks necessários para autenticação via Authentik
     *
     * @return void
     */
    protected function _init() {
        $app = App::i();

        $url = $app->createUrl('auth');
        $config = array_merge([
            'timeout' => '24 hours',           // legado Opauth: aceito no config, ignorado na segurança de fluxo
            'salt' => '',                      // legado Opauth: sem uso no motor novo
            'client_id' => '',
            'client_secret' => '',
            'login_url' => '',
            'logout_url' => $app->createUrl('site','index'),
            'change_password_url' => null,
            'path' => preg_replace('#^https?\:\/\/[^\/]*(/.*)#', '$1', $url),

            'pkce' => 'auto',                  // Authentik suporta PKCE; 'off' somente por config explícita + WARNING
            'state_ttl' => 600,
            'issuer' => env('AUTH_AUTHENTIK_ISSUER', ''),
            'jwks_url' => env('AUTH_AUTHENTIK_JWKS_URL', ''),
            'scope' => env('AUTH_AUTHENTIK_SCOPE', 'openid profile email'),
            'urlAuthorize' => env('AUTH_AUTHENTIK_AUTH_ENDPOINT', ''),
            'urlAccessToken' => env('AUTH_AUTHENTIK_TOKEN_ENDPOINT', ''),
            'urlResourceOwnerDetails' => env('AUTH_AUTHENTIK_USERINFO_ENDPOINT', ''),
        ], $this->_config);

        // typo histórico 'cliente_id' (chave jamais funcional) é corrigido por merge
        if (isset($this->_config['cliente_id']) && !isset($this->_config['client_id'])) {
            $config['client_id'] = $this->_config['cliente_id'];
        }

        // logout federado (end_session_endpoint do OIDC) quando configurado
        $endSession = (string) env('AUTH_AUTHENTIK_END_SESSION_ENDPOINT', '');
        if ($endSession !== '') {
            $config['end_session_endpoint'] = $endSession;
        }

        $this->oauth = new OAuth2ClientHelper('authentik', $config);

        $metadata = [
            'authentik__id' => ['label' => 'Authentik Client ID', 'private' => 'true'],
            'authentik__secret' => ['label' => 'Authentik Client Secret', 'private' => 'true']
        ];

        foreach($metadata as $k => $cfg){
            $def = new \MapasCulturais\Definitions\Metadata($k, $cfg);
            // registro em Subsite permanece comentado como no código original
        }

        $provider = $this->oauth;

        // add actions to auth controller
        // Com login local ATIVO (módulo LocalAuth) e sem MLA, o módulo renderiza
        // a página combinada em hook de prioridade anterior (-100); este redirect
        // ao IdP só roda quando o local está desligado.
        $app->hook('GET(auth.index)', function () use($app){
            if (\LocalAuth\Module::isEnabled() && !\LocalAuth\Module::multipleLocalAuthActive()) {
                return; // o módulo LocalAuth já respondeu (hook -100)
            }
            $app->redirect($this->createUrl('authentik'));
        });

        $app->hook('<<GET|POST>>(auth.authentik)', function () use($app, $provider, $config){
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

        if($config['logout_url'] && php_sapi_name() != "cli"){
            $app->hook('auth.logout:after', function() use($app, $provider, $config){
                // Logout federado OIDC — id_token_hint use-then-clear +
                // post_logout_redirect_uri restrito ao nosso BASE_URL.
                if (!empty($config['end_session_endpoint'])) {
                    $idToken = $provider->consumeIdTokenForLogout();
                    $redirect = $config['end_session_endpoint']
                        . '?id_token_hint=' . urlencode((string) $idToken)
                        . '&post_logout_redirect_uri=' . urlencode($app->getBaseUrl());
                    $app->redirect($redirect);
                }
                $app->redirect($config['logout_url']);
            });
        }

        // Implementa botão para alterar a senha no painel do usuário
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
     * O processamento do callback OIDC acontece em processResponse() (fluxo de
     * requisição); este método apenas resolve a sessão persistida.
     *
     * @return \MapasCulturais\Entities\User|null
     */
    public function _getAuthenticatedUser() {
        $user_id = $_SESSION['auth.oauth.user']['authentik'] ?? null;
        if (!$user_id) {
            return null;
        }
        return App::i()->repo('User')->find($user_id);
    }

    /**
     * Processa a resposta de autenticação do IdP e cria o usuário se não existir.
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
            $values = $token->getValues();
            $idToken = isset($values['id_token']) && is_string($values['id_token']) ? $values['id_token'] : '';

            $claims = [];
            if ($idToken !== '') {
                $claims = $this->oauth->validateIdToken($idToken, $callback['nonce']);
                $this->oauth->storeIdTokenForLogout($idToken);
            }

            // userinfo complementa/atualiza claims (e-mail só com email_verified)
            $resourceOwner = $providerLeague->getResourceOwner($token)->toArray();
            $userinfo = array_merge($claims, $resourceOwner);

            $auth_uid = (string) ($userinfo['sub'] ?? '');
            if ($auth_uid === '') {
                throw new \RuntimeException('auth_failed');
            }

            $auth_provider = $app->getRegisteredAuthProviderId('authentik');
            $user = $app->repo('User')->getByAuth($auth_provider, $auth_uid);

            if (!$user) {
                $user = $this->createUser([
                    'uid' => $auth_uid,
                    'email' => $this->verifiedEmail($userinfo),
                    'name' => (string) ($userinfo['name'] ?? ''),
                ]);
            }

            OAuth2ClientHelper::rotateSession();
            $_SESSION['auth.oauth.user']['authentik'] = $user->id;  // sessão do driver
            $this->_setAuthenticatedUser($user);
            $this->oauth->audit('auth.login.success', $user, $auth_uid);
            $app->applyHook('auth.successful');
            return true;

        } catch (\Throwable $e) {
            // Mensagem genérica ao usuário; detalhe apenas em log.
            $app->log->error('[auth] authentik processResponse: ' . $e->getMessage());
            $this->oauth->audit('auth.login.failed', null, 'exception');
            $this->_setAuthenticatedUser();
            $app->applyHook('auth.failed');
            return false;
        }
    }

    /**
     * E-mail somente com email_verified=true; nunca auto-link por colisão.
     */
    protected function verifiedEmail(array $userinfo): string {
        $email = (string) ($userinfo['email'] ?? '');
        $verified = $userinfo['email_verified'] ?? null;
        if ($email === '') {
            return '';
        }
        if ($verified === true || $verified === 'true' || $verified === 1 || $verified === '1') {
            return $email;
        }
        $allow = filter_var($this->_config['allow_unverified_email'] ?? false, FILTER_VALIDATE_BOOL);
        return $allow ? $email : '';
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
        $user->authProvider = 'authentik';
        $user->authUid = $data['uid'];
        $user->email = $data['email'] !== '' ? $data['email'] : uniqid('authentik-') . '@invalid.local';
        $app->em->persist($user);

        // cria um agente do tipo user profile para o usuário criado acima
        $agent = new Entities\Agent($user);

        $agent->status = 1;

        if(isset($data['name']) && $data['name'] !== ''){
            $agent->name = $data['name'];
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
