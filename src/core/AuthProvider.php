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
        // A sessão local (módulo LocalAuth) resolve primeiro para TODOS os
        // drivers (coexistência local+social); drivers sociais preservam o
        // comportamento dele no _getAuthenticatedUser() quando não há sessão local.
        $this->_authenticatedUser = $this->_resolveLocalSessionUser() ?? $this->_getAuthenticatedUser();
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
     * Extensão aditiva — chaves de sessão próprias, zero interferência com
     * drivers existentes e com o plugin MultipleLocalAuth (que usa
     * 'multipleLocalUserId'): resolve a sessão do login local do core, se houver.
     */
    protected function _resolveLocalSessionUser(): ?Entities\User {
        $user_id = $_SESSION['mapasculturais.auth.local_user_id'] ?? null;
        if (!$user_id) {
            return null;
        }
        $user = App::i()->repo('User')->find($user_id);
        return $user instanceof Entities\User ? $user : null;
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
        // limpa também a sessão local do core (independente do driver)
        unset($_SESSION['mapasculturais.auth.local_user_id']);

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
     *
     * Validação no write time — apenas caminho relativo
     * iniciado por '/', nunca '//' nem scheme/URL absoluta.
     *
     * @param string $redirect_path
     */
    protected function _setRedirectPath(string $redirect_path) {
        $_SESSION['mapasculturais.auth.redirect_path'] = self::sanitizeRedirectPath($redirect_path);
    }

    /**
     * Retorna a URL de redirecionamento pós-autenticação
     *
     * A validação é reaplicada no read time sobre o valor
     * pós-hook 'auth.redirectUrl' (que pode alterar o redirect), imediatamente
     * antes do return — fecha o open redirect B1 também pela porta do hook.
     * 
     * @return string
     */
    protected function getRedirectPath() {
        $app = App::i();
        
        $redirect = $_SESSION['mapasculturais.auth.redirect_path'] ?? $app->createUrl('panel', 'index');

        $app->applyHookBoundTo($this, 'auth.redirectUrl', [&$redirect]);
        
        return self::sanitizeRedirectPath($redirect);
    }

    /**
     * Wrapper PÚBLICO de getRedirectPath() para consumidores fora da
     * hierarquia de drivers (ex.: módulo LocalAuth) — chamar o protected de
     * fora da hierarquia aciona o MagicCallers e lançaria Error no caminho de
     * SUCESSO do login. Aplica a mesma sanitização (read time, pós-hook) e o
     * mesmo comportamento de consumo da sessão.
     */
    public function getRedirectPathForConsumer(): string
    {
        return $this->getRedirectPath();
    }

    /**
     * Normaliza um caminho de redirecionamento para uso interno.
     *
     * Aceita apenas caminhos relativos iniciados por '/' que não começem com
     * '//' e não contenham scheme; qualquer outra coisa (URL absoluta, '//',
     * '\/', scheme-relative) reseta para o painel.
     */
    public static function sanitizeRedirectPath(string $path): string {
        if ($path === '') {
            return self::redirectFallback();
        }
        // caminho relativo: inicia com '/', não com '//' nem '/\'
        if ($path[0] !== '/' || (isset($path[1]) && ($path[1] === '/' || $path[1] === '\\'))) {
            return self::redirectFallback();
        }
        // sem scheme (ex.: 'https:', 'mailto:') no início
        if (preg_match('#^[a-zA-Z][a-zA-Z0-9+.\-]*:#', $path)) {
            return self::redirectFallback();
        }
        return $path;
    }

    /**
     * Fallback seguro do redirect: painel quando o roteador está disponível;
     * '/' caso contrário (CLI/boot inicial, onde não há view/controller).
     */
    private static function redirectFallback(): string {
        try {
            $app = App::i();
            if (isset($app->view) && $app->view !== null) {
                return $app->createUrl('panel', 'index');
            }
        } catch (\Throwable $e) {
            // sem app/roteador utilizável
        }
        return '/';
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
     * Finaliza uma autenticação bem-sucedida DE FORA da hierarquia de drivers
     * Wrapper público com a MESMA semântica de
     * _setAuthenticatedUser — grava o usuário na instância do provider e
     * dispara o hook 'auth.login'.
     *
     * Contexto: módulos do core (LocalAuth) autenticam usuários sem pertencer
     * à hierarquia de AuthProvider; chamar _setAuthenticatedUser (protected
     * final) de fora acionava __call (MagicCallers) e lançava Error APÓS a
     * gravação da sessão. Este método é aditivo (R2): drivers existentes e o
     * plugin MultipleLocalAuth não são afetados.
     */
    public function finalizeAuthentication(Entities\User $user): void {
        $this->_setAuthenticatedUser($user);
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
     * '.uid' não tem consumidor em JS — HttpOnly+Secure. '.adm' é lido pelo
     * tema BaseV1 (mapasculturais.js) — recebe Secure+SameSite; HttpOnly fica
     * pendente de um refactor do tema.
     *
     * @return void
     */
    function setCookies(){
        $user_id = $this->isUserAuthenticated() ? $this->getAuthenticatedUser()->id : 0;
        $user_is_adm = $this->getAuthenticatedUser()->is('admin');
        if (php_sapi_name() != "cli") {
            $secure = (bool) env('MAPAS_HTTPS', false);
            if (filter_var(env('MAPAS_HTTPS', false), FILTER_VALIDATE_BOOLEAN)) {
                $secure = true;
            }
            setcookie('mapasculturais.uid', $user_id, ['expires' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax'] + ($secure ? ['secure' => true] : []));
            setcookie('mapasculturais.adm', $user_is_adm, ['expires' => 0, 'path' => '/', 'samesite' => 'Lax'] + ($secure ? ['secure' => true] : []));
        }
    }
}
