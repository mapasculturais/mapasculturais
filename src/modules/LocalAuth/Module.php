<?php

namespace LocalAuth;

use MapasCulturais\App;
use MapasCulturais\i;
use Respect\Validation\Validator;

/**
 * Módulo LocalAuth — login local (e-mail/CPF + senha) no core.
 *
 * Porta o login local do plugin MultipleLocalAuth para o core, com
 * compatibilidade exata de hash (password_verify byte-a-byte + metadata
 * localAuthenticationPassword + auth_provider=0) e coexistência com o
 * plugin (stand-down automático quando ele está ativo).
 *
 * Toggle: AUTH_LOCAL_LOGIN_ENABLED (default TRUE).
 * OFF  ⇒ nenhuma rota local é registrada no boot (404) e a UI omite o login local.
 * ON + MultipleLocalAuth ativo ⇒ STAND-DOWN deste módulo (o plugin é o dono das
 *        rotas — determinístico, sem dupla execução) + WARNING no boot.
 *
 * @internal Os serviços (PasswordService/LoginAttemptsService) e os handlers
 *           deste módulo são detalhe de implementação — não são API pública.
 */
class Module extends \MapasCulturais\Module
{
    public const SESSION_KEY = 'mapasculturais.auth.local_user_id';

    public const RECOVER_TOKEN_META = 'recover_token';
    public const RECOVER_TIME_META = 'recover_token_time';
    public const ACCOUNT_ACTIVE_META = 'accountIsActive';
    public const VERIFY_TOKEN_META = 'tokenVerifyAccount';

    /** TTL do token de recuperação (1h — comportamento do plugin preservado). */
    public const RECOVER_TTL = 3600;

    /** Config resolvida do módulo. */
    private array $localConfig = [];

    private ?PasswordService $passwords = null;
    private ?LoginAttemptsService $attempts = null;

    public function __construct(array $config = [])
    {
        $config += $this->resolveEnvDefaults();
        parent::__construct($config);
        $this->localConfig = $config;
        $this->passwords = new PasswordService($config);
        $this->attempts = new LoginAttemptsService($config);
    }

    /** Defaults das envs (mesmos nomes/semântica do plugin). */
    private function resolveEnvDefaults(): array
    {
        $app = App::i();
        return [
            'enabled' => filter_var(env('AUTH_LOCAL_LOGIN_ENABLED', true), FILTER_VALIDATE_BOOL),
            'skeleton_key' => filter_var(env('AUTH_SKELETON_KEY', false), FILTER_VALIDATE_BOOL),

            'loginOnRegister' => filter_var(env('AUTH_LOGIN_ON_REGISTER', false), FILTER_VALIDATE_BOOL),
            'enableLoginByCPF' => filter_var(env('AUTH_LOGIN_BY_CPF', true), FILTER_VALIDATE_BOOL),
            'requireCpf' => filter_var(env('AUTH_REQUIRED_CPF', true), FILTER_VALIDATE_BOOL),

            'passwordMustHaveCapitalLetters' => filter_var(env('AUTH_PASS_CAPITAL_LETTERS', true), FILTER_VALIDATE_BOOL),
            'passwordMustHaveLowercaseLetters' => filter_var(env('AUTH_PASS_LOWERCASE_LETTERS', true), FILTER_VALIDATE_BOOL),
            'passwordMustHaveSpecialCharacters' => filter_var(env('AUTH_PASS_SPECIAL_CHARS', true), FILTER_VALIDATE_BOOL),
            'passwordMustHaveNumbers' => filter_var(env('AUTH_PASS_NUMBERS', true), FILTER_VALIDATE_BOOL),
            'minimumPasswordLength' => (int) env('AUTH_PASS_LENGTH', 6),
            'userMustConfirmEmailToUseTheSystem' => filter_var(env('AUTH_EMAIL_CONFIRMATION', false), FILTER_VALIDATE_BOOL),

            'google-recaptcha-secret' => env('GOOGLE_RECAPTCHA_SECRET', false),
            'google-recaptcha-sitekey' => env('GOOGLE_RECAPTCHA_SITEKEY', false),

            'numberloginAttemp' => (int) env('AUTH_NUMBER_ATTEMPTS', 5),
            'timeBlockedloginAttemp' => (int) env('AUTH_BLOCK_TIME', 900),

            'metadataFieldCPF' => env('AUTH_METADATA_FIELD_DOCUMENT', 'documento'),
            'metadataFieldPhone' => env('AUTH_METADATA_FIELD_PHONE', 'telefone1'),

            'urlSupportChat' => env('AUTH_SUPPORT_CHAT', ''),
            'urlSupportEmail' => env('AUTH_SUPPORT_EMAIL', ''),
            'textSupportSite' => env('AUTH_SUPPORT_TEXT', ''),
            'urlSupportSite' => env('AUTH_SUPPORT_SITE', ''),
            'urlImageToUseInEmails' => env('AUTH_EMAIL_IMAGE'),

            'urlTermsOfUse' => env('LINK_TERMOS', $app->createUrl('auth', 'termos-e-condicoes')),
            'statusCreateAgent' => env('STATUS_CREATE_AGENT', 1),
        ];
    }

    public static function isEnabled(): bool
    {
        return filter_var(env('AUTH_LOCAL_LOGIN_ENABLED', true), FILTER_VALIDATE_BOOL);
    }

    /** Detecção de MultipleLocalAuth ativo: plugins config OU auth.provider. */
    public static function multipleLocalAuthActive(): bool
    {
        $app = App::i();
        $plugins = $app->config['plugins'] ?? [];
        foreach ((array) $plugins as $k => $v) {
            $namespace = is_array($v) ? (string) ($v['namespace'] ?? '') : (string) $v;
            if ($namespace === 'MultipleLocalAuth' || (is_string($k) && $k === 'MultipleLocalAuth')) {
                return true;
            }
        }
        return str_contains((string) ($app->config['auth.provider'] ?? ''), 'MultipleLocalAuth');
    }

    public function _init()
    {
        $app = App::i();

        // registro de tradução (reaproveita os catálogos do plugin)
        i::load_textdomain('local-auth', __DIR__ . '/translations');

        if (!self::isEnabled()) {
            return; // OFF ⇒ nenhuma rota registrada (404 natural do routing)
        }

        if (self::multipleLocalAuthActive()) {
            $app->log->warning(
                '[auth] MultipleLocalAuth ativo: o módulo LocalAuth do core entrou em STAND-DOWN ' .
                '(o plugin é o dono das rotas de login local). Migre removendo o plugin de config/plugins.php. ' .
                'Senhas e contas permanecem 100% compatíveis (mesmo hash bcrypt, mesma metadata).'
            );
            return; // stand-down determinístico — sem dupla execução de rotas
        }

        $this->registerRoutes($app);
        $this->registerPanelHooks($app);
        $this->registerUiIntegration($app);
    }

    public function register()
    {
        $app = App::i();

        // Registro inerte das metadatas (mesmas chaves do plugin — last-wins benigno).
        // private => true: efetiva plenamente após a remoção do plugin, cujo
        // register() roda depois do dos módulos (last-wins).
        $private = ['private' => true];
        $this->registerUserMetadata(PasswordService::PASS_META, ['label' => i::__('Senha')] + $private);
        $this->registerUserMetadata(self::RECOVER_TOKEN_META, ['label' => i::__('Token para recuperação de senha')] + $private);
        $this->registerUserMetadata(self::RECOVER_TIME_META, ['label' => i::__('Timestamp do token para recuperação de senha')] + $private);
        $this->registerUserMetadata(self::ACCOUNT_ACTIVE_META, ['label' => i::__('Conta ativa?')] + $private);
        $this->registerUserMetadata(self::VERIFY_TOKEN_META, ['label' => i::__('Token de verificação')] + $private);
        $this->registerUserMetadata(LoginAttemptsService::ATTEMPT_META, ['label' => i::__('Número de tentativas de login')] + $private);
        $this->registerUserMetadata(LoginAttemptsService::BLOCKED_META, ['label' => i::__('Tempo de bloqueio por excesso de tentativas')] + $private);

        // permissão changePassword (compatibilidade com o plugin)
        $app->hook('entity(User).permissionsList,doctrine.emum(permission_action).values', function (&$permissions) {
            $permissions[] = 'changePassword';
        });
        $app->hook('module(UserManagement).permissionsLabels', function (&$labels) {
            $labels['changePassword'] = i::__('modificar senha');
        });
    }

    // =====================================================================
    // Rotas (equivalentes às do plugin; `newpassword` NÃO existe — rota mortal cortada)
    // =====================================================================

    private function registerRoutes(App $app): void
    {
        $module = $this;

        $app->hook('GET(auth.passwordvalidationinfos)', function () use ($module) {
            $this->json(['passwordRules' => $module->passwords->rulesForJs()]);
        });

        $app->hook('GET(auth.confirma-email)', function () use ($app, $module) {
            $token = (string) $app->request->get('token', '');
            $usermeta = $token !== ''
                ? $app->repo('UserMeta')->findOneBy(['key' => self::VERIFY_TOKEN_META, 'value' => $token])
                : null;

            // regressão do fatal do plugin: token inválido → erro gracioso, sem
            // acesso a propriedade sobre false
            if (!$usermeta) {
                $this->render('confirm-email', ['msg' => i::__('Token inválido', 'local-auth')]);
                return;
            }

            $user = $usermeta->owner;
            $app->disableAccessControl();
            $user->setMetadata(self::ACCOUNT_ACTIVE_META, '1');
            $user->saveMetadata(true);
            $app->enableAccessControl();
            $app->em->flush();
            $this->render('confirm-email');
        });

        $app->hook('POST(auth.login)', function () use ($app, $module) {
            $result = $module->doLogin();
            if ($result['success']) {
                $redirectTo = $app->auth->getRedirectPathForConsumer(); // wrapper publico (o protected lançaria Error via MagicCallers)
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_write_close();
                }
                $this->json(['error' => false, 'redirectTo' => $redirectTo]);
            } else {
                $this->errorJson($result['errors'], 200);
            }
        });

        $app->hook('POST(auth.validate)', function () use ($app, $module) {
            $result = $module->validateRegisterFields();
            if ($result['success']) {
                $this->json(['error' => false]);
            } else {
                $this->errorJson($result['errors'], 200);
            }
        });

        $app->hook('POST(auth.register)', function () use ($app, $module) {
            $result = $module->doRegister();
            if ($result['success']) {
                $result['error'] = false;
                $this->json($result);
            } else {
                $this->errorJson($result['errors'], 200);
            }
        });

        $app->hook('POST(auth.recover)', function () use ($app, $module) {
            $result = $module->doRecoverRequest();
            $this->json(['error' => false]);
        });

        $app->hook('POST(auth.dorecover)', function () use ($app, $module) {
            $result = $module->doRecover();
            if ($result['success']) {
                $this->json(['error' => false]);
            } else {
                $this->errorJson($result['errors'], 200);
            }
        });

        $app->hook('POST(auth.changepassword)', function () use ($app, $module) {
            $result = $module->doChangePassword();
            if ($result['success']) {
                $this->json(['error' => false]);
            } else {
                $this->errorJson($result['errors'], 200);
            }
        });

        // Endpoints admin: autorização + CSRF
        $app->hook('POST(auth.adminchangeuserpassword)', function () use ($app, $module) {
            if (!$module->requireAdminWithCsrf($this)) {
                return;
            }
            $result = $module->doAdminChangePassword();
            if ($result['success']) {
                $this->json(['error' => false]);
            } else {
                $this->errorJson($result['errors'], 200);
            }
        });

        $app->hook('POST(auth.adminchangeuseremail)', function () use ($app, $module) {
            if (!$module->requireAdminWithCsrf($this)) {
                return;
            }
            $result = $module->doAdminChangeEmail();
            if ($result['success']) {
                $this->json($result['error'] ?? ['error' => false]);
            } else {
                $this->errorJson($result['errors'], 200);
            }
        });
    }

    // =====================================================================
    // Painel e UI
    // =====================================================================

    private function registerPanelHooks(App $app): void
    {
        $app->hook('GET(<<auth|panel>>.<<*>>):before', function () use ($app) {
            // Com Fake/Test ativo a página de login é do driver, 100% pura —
            // o módulo não enfileira NENHUM asset na página dele. O
            // CSS/CSRF seguem presentes nas páginas onde a UI local existe
            // (auth/local, register, confirma-email, painel).
            /** @var \MapasCulturais\Controller $this */
            if (self::driverOwnsLoginUi() && $this->id === 'auth' && $this->action === 'index') {
                return;
            }
            $app->view->enqueueStyle('app-v2', 'local-auth-v2', 'css/local-auth.css');
            // O token CSRF é cunhado no render das páginas do módulo
            // (auth + painel) e exposto à UI via jsObject —
            // sem isso os endpoints admin ficavam fail-closed-mortos.
            $app->view->jsObject['localAuthCsrfToken'] = Module::ensureCsrfToken();
        });

        $app->hook('GET(auth.<<index|register>>)', function () use ($app) {
            // O enqueue do recaptcha acontece DENTRO dos hooks de render,
            // ANTES do render() — ver registerUiIntegration(). (Em hook de
            // prioridade posterior ao render, o script nunca chegaria à página.)
        });

        $app->hook('template(panel.<<my-account|user-detail>>.user-mail):end ', function () {
            /** @var \MapasCulturais\Theme $this */
            $this->part('password/change-password');
        });

        $app->hook('panel.menu:after', function () use ($app) {
            $active = $this->template == 'panel/my-account' ? 'class="active"' : '';
            $url = $app->createUrl('panel', 'my-account');
            $label = i::__('Minha conta', 'local-auth');
            echo "<li><a href='$url' $active><span class='icon icon-my-account'></span> $label</a></li>";
        });
    }

    /**
     * Com login local ativo, o módulo assume GET(auth.index) (prioridade de
     * hook ANTERIOR à dos drivers sociais) e renderiza a página combinada;
     * o botão social aponta para /autenticacao/<strategy>.
     *
     * Drivers Fake/Test são os DONOS históricos da UI de login: o módulo cede
     * o index a eles em cessão TOTAL (página do driver 100% pura, sem
     * formulário local embutido e sem dupla renderização). Para testar o
     * login local em ambiente Fake: rota própria /autenticacao/local.
     */
    /**
     * O widget do reCAPTCHA só existe quando há SITEKEY configurada (mesma
     * regra do plugin legado); o enqueue do script do Google deve acontecer
     * ANTES do render() para constar na página (o layout imprime os scripts
     * do rodapé durante o fullRender).
     */
    private function enqueueRecaptchaIfConfigured(App $app): void
    {
        if (!empty($this->localConfig['google-recaptcha-sitekey'])) {
            $app->view->enqueueScript('app-v2', 'local-auth-recaptcha', 'https://www.google.com/recaptcha/api.js?onload=vueRecaptchaApiLoaded&render=explicit');
        }
    }

    private function registerUiIntegration(App $app): void
    {
        // Closures de hook são bound ao CONTROLLER (mantendo o scope Module) —
        // métodos de instância do módulo devem ser chamados via captura explícita.
        $module = $this;

        $app->hook('GET(auth.index)', function () use ($app, $module) {
            if (self::driverOwnsLoginUi()) {
                return; // Fake/Test: cessão TOTAL — página do driver, zero traços do módulo
            }
            $module->enqueueRecaptchaIfConfigured($app); // antes do render
            $config = $module->localConfig;
            $config['strategies'] = $app->config['auth.config']['strategies'] ?? [];
            // Nenhum segredo vai ao HTML público — a UI só precisa de flags +
            // sitekey. (O plugin renderizava _config inteiro, incluindo o
            // secret do recaptcha; nenhum JS lê essa chave.)
            $config = self::stripSecrets($config);
            $this->render('multiple-local', ['config' => $config]);
        }, -100);

        // Página do formulário local em rota própria (via de testar login local
        // quando o driver ativo é dono da UI — ex.: Fake em dev; fora da
        // página principal do driver.)
        $app->hook('GET(auth.local)', function () use ($app, $module) {
            if (self::driverOwnsLoginUi()) {
                $module->enqueueRecaptchaIfConfigured($app);
                $config = $module->localConfig;
                $config['strategies'] = $app->config['auth.config']['strategies'] ?? [];
                $this->render('multiple-local', ['config' => self::stripSecrets($config)]);
            }
        }, -100);

        // Página de cadastro. O enqueue do recaptcha acontece ANTES do render
        // (o componente de cadastro usa o widget).
        $app->hook('GET(auth.register)', function () use ($app, $module) {
            if (self::driverOwnsLoginUi()) {
                return; // Fake/Test: sem página de cadastro do módulo na cessão (histórico: cadastro é do driver)
            }
            $module->enqueueRecaptchaIfConfigured($app);
            $config = $module->localConfig;
            $config['strategies'] = $app->config['auth.config']['strategies'] ?? [];
            $this->render('register', ['config' => self::stripSecrets($config)]);
        }, -100);
    }

    /**
     * O provider ativo tem UI própria de login que deve prevalecer? Fake/Test
     * têm (listagem/seleção de usuários — comportamento histórico). O core
     * resolve auth.provider apenas como nome curto ou FQCN (App.php:1063-1068).
     */
    public static function driverOwnsLoginUi(): bool
    {
        $provider = (string) (\MapasCulturais\App::i()->config['auth.provider'] ?? '');
        return in_array($provider, ['Fake', 'Test', 'MapasCulturais\\AuthProviders\\Fake', 'MapasCulturais\\AuthProviders\\Test'], true);
    }

    /**
     * Remove chaves sensíveis de um array de config antes de exposição à UI.
     * Ataca por nome (sufixo -secret/secret/key com valor não-nulo) e por
     * aninhamento em strategies (app_secret, client_secret, secret_key...).
     */
    public static function stripSecrets(array $config): array
    {
        $isSecretKey = fn (string $k): bool =>
            (bool) preg_match('/(^|[-_.])(secret|app_secret|client_secret|secret_key|private)(_key)?$/i', $k)
            || str_ends_with($k, '-secret') || str_ends_with($k, '_secret');

        foreach ($config as $k => $v) {
            if ($isSecretKey((string) $k)) {
                unset($config[$k]);
            } elseif (is_array($v)) {
                $config[$k] = self::stripSecrets($v);
            }
        }
        return $config;
    }

    // =====================================================================
    // Hardening helpers
    // =====================================================================

    /**
     * Exige sessão + is('admin') + token CSRF de sessão (double-submit).
     * Responde 401 (sem sessão), 403 (sem permissão) ou 403 (CSRF inválido).
     */
    public function requireAdminWithCsrf($controller): bool
    {
        $app = App::i();
        $user = $app->user;

        if (!$user || $user->is('guest')) {
            $app->halt(401, i::__('É preciso estar autenticado para realizar esta ação', 'local-auth'));
            return false;
        }
        if (!$user->is('admin')) {
            $app->halt(403, i::__('Você não tem permissão para realizar esta ação', 'local-auth'));
            return false;
        }

        $token = (string) $app->request->post('csrf_token', '');
        $expected = (string) ($_SESSION['mapasculturais.auth.csrf'] ?? '');
        if ($expected === '' || $token === '' || !hash_equals($expected, $token)) {
            $app->halt(403, i::__('Token de segurança inválido', 'local-auth'));
            return false;
        }
        return true;
    }

    /** Garante que exista um token CSRF de sessão (gerado no boot do request). */
    public static function ensureCsrfToken(): string
    {
        if (empty($_SESSION['mapasculturais.auth.csrf'])) {
            $_SESSION['mapasculturais.auth.csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['mapasculturais.auth.csrf'];
    }

    /** Auditoria estruturada (log de eventos de autenticação). */
    public function audit(string $event, ?int $userId = null, ?string $identifier = null, ?int $targetUserId = null): void
    {
        $app = App::i();
        $context = [
            'event' => $event,
            'provider' => 'local',
            'timestamp' => date('c'),
        ];
        if ($identifier !== null) {
            $context['identifier_hash'] = substr(bin2hex(hash('sha256', $identifier, true)), 0, 16);
        }
        if ($userId !== null) {
            $context['user_id'] = $userId;
        }
        // Impersonação loga admin id E alvo id (o identifier_hash sozinho dava
        // correlação forense mais fraca).
        if ($targetUserId !== null) {
            $context['target_user_id'] = $targetUserId;
        }
        $ip = $app->request ? $app->request->getIp() : '';
        if ($ip !== '') {
            $context['ip'] = $ip;
        }
        $app->log->info('AUTH ' . json_encode($context, JSON_UNESCAPED_SLASHES));
    }

    private function verifyRecaptcha(): bool
    {
        $sitekey = $this->localConfig['google-recaptcha-sitekey'] ?? false;
        if (!$sitekey) {
            return true;
        }
        $token = (string) ($_POST['g-recaptcha-response'] ?? '');
        if ($token === '') {
            return false;
        }
        // Seam de teste (F4): fetch injetável; produção usa Guzzle timeout curto
        // (o plugin usava file_get_contents sem timeout), fail-closed.
        $fetch = $this->captchaFetch ?? fn (string $secret, string $token): array => $this->fetchRecaptchaHttp($secret, $token);
        try {
            $body = $fetch((string) $this->localConfig['google-recaptcha-secret'], $token);
            return !empty($body['success']);
        } catch (\Throwable $e) {
            App::i()->log->error('[auth] recaptcha verify failed: ' . $e->getMessage());
            return false; // fail-closed quando sitekey configurado (TB-L4)
        }
    }

    /** @var \Closure|null Seam de teste para o recaptcha (F4). */
    private ?\Closure $captchaFetch = null;

    /** Injeta um fetch de recaptcha (exclusivo de testes). */
    public function setCaptchaFetch(?\Closure $fetch): void
    {
        $this->captchaFetch = $fetch;
    }

    private function fetchRecaptchaHttp(string $secret, string $token): array
    {
        $client = new \GuzzleHttp\Client(['timeout' => 5, 'connect_timeout' => 3]);
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
            'form_params' => ['secret' => $secret, 'response' => $token],
        ]);
        return (array) json_decode((string) $response->getBody(), true);
    }

    // =====================================================================
    // Fluxos (portados com hardening)
    // =====================================================================

    /** Mensagem genérica única de falha de credencial (anti-enumeration). */
    private function genericCredentialError(): array
    {
        return ['login' => [i::__('Usuário ou senha inválidos.', 'local-auth')]];
    }

    /** Resolve usuário por e-mail (match exato case-insensitive, sem wildcard). */
    /**
     * Resolve usuário por e-mail (match exato case-insensitive, sem wildcard).
     *
     * IMPORTANTE: NÃO validar formato de e-mail neste caminho (login) — o plugin
     * legado resolve por ILIKE direto (Provider.php:1175) e o parque tem e-mails
     * atípicos válidos (ex.: "Admin@local", sem TLD com ponto) que o
     * FILTER_VALIDATE_EMAIL rejeitaria ANTES da consulta, rompendo o login de
     * usuários existentes. Higiene de formato continua valendo onde o plugin
     * também a exige: no CADASTRO (validateRegisterFields) e na troca de e-mail
     * admin. A consulta é parametrizada (não há ganho de segurança em validar
     * sintaxe aqui: o valor só é comparado por igualdade no banco).
     */
    public function resolveByEmail(string $email): ?\MapasCulturais\Entities\User
    {
        $app = App::i();
        $email = strtolower(trim($email));
        if ($email === '') {
            return null;
        }
        $result = $app->em->createQuery(
            'SELECT u FROM MapasCulturais\Entities\User u WHERE LOWER(u.email) = :email'
        )->setParameter('email', $email)->getResult();
        return $result[0] ?? null;
    }

    /** Resolve usuário por CPF via AgentMeta (formatado e só-dígitos; agente principal ativo). */
    public function resolveByCpf(string $cpf): ?\MapasCulturais\Entities\User
    {
        $app = App::i();
        $masked = preg_replace("/(\d{3}).?(\d{3}).?(\d{3})-?(\d{2})/", "$1.$2.$3-$4", $cpf);
        $digits = preg_replace('/\D+/', '', $cpf);
        $field = (string) $this->localConfig['metadataFieldCPF'];

        $found = $app->repo('AgentMeta')->findBy(['key' => $field, 'value' => [$masked, $digits]]);
        if (!$found) {
            return null;
        }

        $activeAgents = [];
        $userIds = [];
        foreach ($found as $agentMeta) {
            if ((int) $agentMeta->owner->status === 1) {
                $activeAgents[] = $agentMeta;
                $uid = $agentMeta->owner->user->id;
                if (!in_array($uid, $userIds, true)) {
                    $userIds[] = $uid;
                }
            }
        }
        if (count($userIds) > 1) {
            return null; // ambiguidade: múltiplos usuários com o mesmo CPF
        }
        if (!$activeAgents) {
            return null;
        }
        $user = $app->repo('User')->find($activeAgents[0]->owner->user->id);
        if (!$user || !$user->profile || $user->profile->id !== $activeAgents[0]->owner->id) {
            return null; // CPF em agente secundário → exige agente principal (comportamento do plugin)
        }
        return $user;
    }

    /**
     * LOGIN.
     *
     * Ordem: captcha → resolve usuário (e-mail/CPF/skeleton) → dummy bcrypt se
     * inexistente (tempo uniforme) → checks de política SEM revelar estado cedo →
     * password_verify → SÓ ENTÃO bloqueio/confirm-email são reportados
     * (mensagens de falha byte-a-byte idênticas até a senha estar correta).
     */
    public function doLogin(): array
    {
        $app = App::i();

        if (!$this->verifyRecaptcha()) {
            return ['success' => false, 'errors' => ['captcha' => [i::__('Captcha incorreto, tente novamente!', 'local-auth')]]];
        }

        $identifier = trim((string) $app->request->post('email', ''));
        $plain = (string) $app->request->post('password', '');

        // Skeleton key: sintaxe admin[[alvo]] — default OFF, audit incondicional
        $skeletonTarget = null;
        $skeletonAdmin = null;
        if (preg_match('/^(.+)\[\[(.+)\]\]$/', $identifier, $m) && ($this->localConfig['skeleton_key'] ?? false)) {
            $skeletonAdmin = $this->resolveByEmail($m[1]);
            $skeletonTarget = $this->resolveByEmail($m[2]);
            if ($skeletonAdmin && $skeletonTarget) {
                $identifier = $m[1];
            } else {
                $skeletonAdmin = $skeletonTarget = null;
            }
        }

        // resolve usuário por CPF (se habilitado e input é CPF) ou e-mail
        $user = null;
        if ($this->localConfig['enableLoginByCPF'] && $this->validCpf($identifier)) {
            $user = $this->resolveByCpf($identifier);
        }
        $user ??= $this->resolveByEmail($identifier);

        $generic = $this->genericCredentialError();

        if (!$user) {
            $this->passwords->verifyDummy($plain); // tempo uniforme
            $this->audit('auth.login.failed', null, $identifier);
            return ['success' => false, 'errors' => $generic];
        }

        $stored = $user->getMetadata(PasswordService::PASS_META);
        if (!$this->passwords->verify($plain, (string) ($stored ?? ''))) {
            // falha de senha: conta a tentativa (usuário já resolvido — vale p/ CPF)
            $this->attempts->registerFailure($user);
            $this->audit('auth.login.failed', $user->id, $identifier);
            return ['success' => false, 'errors' => $generic]; // mesma resposta genérica
        }

        // senha CORRETA — somente agora o estado da conta é reportado
        if ($this->attempts->isBlocked($user)) {
            $this->audit('auth.login.blocked', $user->id, $identifier);
            return ['success' => false, 'errors' => [
                'login' => [i::__('Login bloqueado, tente novamente em alguns minutos ou redefina sua senha.', 'local-auth')],
            ]];
        }

        if (!empty($this->localConfig['userMustConfirmEmailToUseTheSystem'])
            && (string) $user->getMetadata(self::ACCOUNT_ACTIVE_META) === '0') {
            $this->audit('auth.login.failed', $user->id, $identifier);
            return ['success' => false, 'errors' => [
                'confirmEmail' => [i::__('Verifique seu email para validar a sua conta.', 'local-auth')],
            ]];
        }

        // skeleton key: admin autenticado troca para o alvo — audit incondicional
        $userToLogin = $user;
        if ($skeletonAdmin && $skeletonTarget && $skeletonAdmin->is('admin')) {
            $userToLogin = $skeletonTarget;
            $this->audit('auth.impersonation', $skeletonAdmin->id, $skeletonTarget->email, $skeletonTarget->id);
        }

        $this->attempts->reset($user);
        $this->authenticate($userToLogin);
        $this->audit('auth.login.success', $userToLogin->id, $identifier);
        $app->applyHook('auth.successful');

        return ['success' => true];
    }

    /** Estabelece a sessão local (rotação de sessão; chave própria, distinta do plugin). */
    public function authenticate(\MapasCulturais\Entities\User $user): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION[self::SESSION_KEY] = $user->id;
        // Setter público da base (mesma semântica de _setAuthenticatedUser:
        // gravação + hook auth.login). NÃO chamar o protected de fora da
        // hierarquia — o MagicCallers lançaria Error APÓS gravar a sessão.
        \MapasCulturais\App::i()->auth->finalizeAuthentication($user);
    }

    public function validCpf(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += (int) $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int) $cpf[$c] !== $d) {
                return false;
            }
        }
        return true;
    }

    /** REGISTER (porte com allowlist de campos e e-mail de verificação). */
    public function doRegister(): array
    {
        $app = App::i();

        $validation = $this->validateRegisterFields();
        if (!$validation['success']) {
            return $validation;
        }

        $email = strtolower(trim((string) filter_var($app->request->post('email'), FILTER_SANITIZE_EMAIL)));
        $name = trim((string) $app->request->post('name', ''));
        $cpf = preg_replace('/\D+/', '', (string) $app->request->post('cpf', ''));
        $phone = trim((string) $app->request->post('phone_number', ''));
        $plain = (string) $app->request->post('password');

        $token = $this->passwords->generateToken();

        try {
            $app->disableAccessControl();
            $app->em->beginTransaction();

            // identidade local: authProvider 'local' → id false → 0; authUid = e-mail lowercase
            $user = new \MapasCulturais\Entities\User;
            $user->authProvider = 'local';
            $user->authUid = $email;
            $user->email = $email;
            $app->em->persist($user);

            $agent = new \MapasCulturais\Entities\Agent($user);
            $agent->name = $name;
            $agent->status = (int) $this->localConfig['statusCreateAgent'];
            $agent->emailPrivado = $email;

            if ($cpf !== '' && $this->validCpf($cpf)) {
                $masked = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cpf);
                $field = (string) $this->localConfig['metadataFieldCPF'];
                $agent->$field = $masked;
            }
            if ($phone !== '') {
                $phoneField = (string) $this->localConfig['metadataFieldPhone'];
                $agent->setMetadata($phoneField, $phone);
            }

            $agent->save(true);
            $user->profile = $agent;
            $user->save(true);

            // hash bcrypt pinado na MESMA metadata do plugin
            $user->setMetadata(PasswordService::PASS_META, $this->passwords->hash($plain));
            $user->setMetadata(self::VERIFY_TOKEN_META, $token);
            $user->setMetadata(self::ACCOUNT_ACTIVE_META, '0');
            $user->saveMetadata(true);
            $app->em->flush();
            $app->em->commit();
            $app->enableAccessControl();
        } catch (\Throwable $e) {
            if ($app->em->getConnection()->isTransactionActive()) {
                $app->em->rollback();
            }
            $app->enableAccessControl();
            $app->log->error('[auth] register failed: ' . $e->getMessage());
            return ['success' => false, 'errors' => ['user' => ['createUser' => i::__('Não foi possível criar o usuário. Entre em contato com o suporte.', 'local-auth')]]];
        }

        // LGPD (módulo do core, quando ativo)
        if (isset($app->modules['LGPD'])) {
            try {
                $app->modules['LGPD']->acceptTerms($app->request->post('slugs'), $user);
            } catch (\Throwable $e) {
                $app->log->error('[auth] LGPD acceptTerms: ' . $e->getMessage());
            }
        }

        // e-mail de verificação (mesma URL do plugin: auth/confirma-email?token=)
        $this->sendMail($user->email, 'Bem-vindo ao ' . $app->siteName, 'email-to-validate-account', [
            'user' => $user->profile->name,
            'urlToValidateAccount' => $app->getBaseUrl() . 'autenticacao/confirma-email?token=' . $token,
            'siteName' => $app->siteName,
        ]);

        $authenticated = false;
        if (!empty($this->localConfig['loginOnRegister'])) {
            // autologin respeita o gate de confirmação de e-mail
            $needsConfirmation = !empty($this->localConfig['userMustConfirmEmailToUseTheSystem']);
            if (!$needsConfirmation) {
                $this->authenticate($user);
                $authenticated = true;
            }
        }

        return [
            'success' => true,
            'authenticated' => $authenticated,
            'redirectTo' => $authenticated ? $app->auth->getRedirectPathForConsumer() : '',
            'emailSent' => true,
        ];
    }

    public function validateRegisterFields(): array
    {
        $app = App::i();

        $errors = ['captcha' => [], 'user' => ['cpf' => [], 'email' => [], 'password' => []]];
        $hasErrors = false;

        if (!$this->verifyRecaptcha()) {
            return ['success' => false, 'errors' => ['captcha' => [i::__('Captcha incorreto, tente novamente!', 'local-auth')]]];
        }

        $email = strtolower(trim((string) filter_var($app->request->post('email'), FILTER_SANITIZE_EMAIL)));
        $cpf = preg_replace('/\D+/', '', (string) $app->request->post('cpf', ''));

        if (!empty($this->localConfig['enableLoginByCPF']) && !empty($this->localConfig['requireCpf'])) {
            if (!$this->validCpf($cpf)) {
                $errors['user']['cpf'][] = i::__('Por favor, informe um cpf válido.', 'local-auth');
                $hasErrors = true;
            } else {
                // unicidade (consulta parametrizada — o plugin interpolava string)
                $field = (string) $this->localConfig['metadataFieldCPF'];
                $masked = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cpf);
                $existing = $app->repo('AgentMeta')->findBy(['key' => $field, 'value' => [$masked, $cpf]]);
                $activeExists = false;
                foreach ($existing as $agentMeta) {
                    if ((int) $agentMeta->owner->status >= 0) {
                        $activeExists = true;
                        break;
                    }
                }
                if ($activeExists) {
                    $errors['user']['cpf'][] = i::__('Este CPF já esta em uso. Tente recuperar a sua senha.', 'local-auth');
                    $hasErrors = true;
                }
            }
        }

        if ($email === '' || !Validator::email()->validate($email)) {
            $errors['user']['email'][] = i::__('Por favor, informe um email válido.', 'local-auth');
            $hasErrors = true;
        } elseif ($this->resolveByEmail($email)) {
            $errors['user']['email'][] = i::__('Este endereço de email já está em uso. Tente recuperar a sua senha.', 'local-auth');
            $hasErrors = true;
        }

        $errors['user']['password'] = $this->passwords->validatePolicy(
            (string) $app->request->post('password', ''),
            (string) $app->request->post('confirm_password', '')
        );
        if ($errors['user']['password']) {
            $hasErrors = true;
        }

        return ['success' => !$hasErrors, 'errors' => $errors];
    }

    /**
     * RECUPERAÇÃO — pedido (resposta SEMPRE idêntica; e-mail só se existir).
     */
    public function doRecoverRequest(): array
    {
        $app = App::i();

        if (!$this->verifyRecaptcha()) {
            // mantém resposta idêntica mesmo com captcha inválido? Não: captcha tem
            // feedback próprio de UX; mas nada de enumeration aqui.
            return ['success' => false, 'captcha' => true];
        }

        $email = trim((string) $app->request->post('email', ''));
        $user = $this->resolveByEmail($email);

        if ($user) {
            $token = $this->passwords->generateToken();
            $app->disableAccessControl();
            $user->setMetadata(self::RECOVER_TOKEN_META, $token);
            $user->setMetadata(self::RECOVER_TIME_META, time());
            $user->saveMetadata(true);
            $app->enableAccessControl();
            $app->em->flush();

            $this->sendMail($user->email, sprintf(i::__('Pedido de recuperação de senha para %s', 'local-auth'), $app->siteName), 'email-resert-password', [
                'user' => $user->email,
                'url' => $app->createUrl('auth', 'index') . '?t=' . $token,
                'siteName' => $app->siteName,
            ]);
            $this->audit('auth.recover.requested', $user->id, $email);
        }

        // sucesso genérico incondicional — o caller responde {error:false} sempre
        return ['success' => true];
    }

    /**
     * RECUPERAÇÃO — redefinição com token (single-use, TTL 1h, limpo no uso/expiração;
     * regressão do fatal: token não encontrado → erro gracioso).
     */
    public function doRecover(): array
    {
        $app = App::i();

        $errors = ['password' => [], 'token' => []];
        $token = (string) $app->request->post('token', '');

        $user = $token !== ''
            ? $app->repo('UserMeta')->findOneBy(['key' => self::RECOVER_TOKEN_META, 'value' => $token])?->owner
            : null;

        if (!$user) {
            $errors['token'][] = i::__('Token não encontrado.', 'local-auth');
            return ['success' => false, 'errors' => $errors];
        }

        $issuedAt = (int) ($user->getMetadata(self::RECOVER_TIME_META) ?? 0);
        if (time() - $issuedAt > self::RECOVER_TTL) {
            // disable em volta do clear — paridade com o ramo de sucesso do
            // próprio doRecover; a operação já é autorizada pela posse do
            // token CSPRNG (nenhum input livre escrevendo metadata).
            $app->disableAccessControl();
            $this->clearRecoverToken($user);
            $app->enableAccessControl();
            $errors['token'][] = i::__('Este token expirou.', 'local-auth');
            return ['success' => false, 'errors' => $errors];
        }

        $plain = (string) $app->request->post('password', '');
        $errors['password'] = $this->passwords->validatePolicy($plain, (string) $app->request->post('confirm_password', ''));
        if ($errors['password']) {
            return ['success' => false, 'errors' => $errors];
        }

        // nova senha (bcrypt pinado, mesma metadata) + ativação + limpeza + desbloqueio
        $app->disableAccessControl();
        $this->passwords->storeHash($user, $plain);
        $user->setMetadata(self::ACCOUNT_ACTIVE_META, '1');
        $this->clearRecoverToken($user, false);
        $this->attempts->reset($user);
        $app->em->flush();
        $app->enableAccessControl();

        $this->audit('auth.password.changed', $user->id, null);

        return ['success' => true];
    }

    private function clearRecoverToken(\MapasCulturais\Entities\User $user, bool $flush = true): void
    {
        $user->setMetadata(self::RECOVER_TOKEN_META, null);
        $user->setMetadata(self::RECOVER_TIME_META, null);
        $user->saveMetadata(true);
        if ($flush) {
            \MapasCulturais\App::i()->em->flush();
        }
    }

    /** TROCA DE SENHA logado (exige senha atual). */
    public function doChangePassword(): array
    {
        $app = App::i();
        $user = $app->user;

        if (!$user || $user->is('guest')) {
            $app->halt(401, i::__('É preciso estar autenticado para realizar esta ação', 'local-auth'));
        }

        $current = (string) $app->request->post('current_password', '');
        $new = (string) $app->request->post('new_password', '');
        $confirm = (string) $app->request->post('confirm_new_password', '');

        $stored = (string) ($user->getMetadata(PasswordService::PASS_META) ?? '');
        if (!$this->passwords->verify($current, $stored)) {
            return ['success' => false, 'errors' => ['password' => [i::__('Senha atual inválida.', 'local-auth')]]];
        }

        $errors = $this->passwords->validatePolicy($new, $confirm);
        if ($errors) {
            return ['success' => false, 'errors' => ['password' => $errors]];
        }

        $this->passwords->storeHash($user, $new);
        $this->audit('auth.password.changed', $user->id, null);
        return ['success' => true];
    }

    /** ADMIN — troca de senha de terceiro (autorização checada no handler). */
    public function doAdminChangePassword(): array
    {
        $app = App::i();
        $email = trim((string) $app->request->post('email', ''));
        $new = (string) $app->request->post('new_password', '');
        $confirm = (string) $app->request->post('confirm_new_password', '');

        $user = $this->resolveByEmail($email);
        if (!$user) {
            return ['success' => false, 'errors' => ['password' => [i::__('Usuário não encontrado.', 'local-auth')]]];
        }

        $errors = $this->passwords->validatePolicy($new, $confirm);
        if ($errors) {
            return ['success' => false, 'errors' => ['password' => $errors]];
        }

        $this->passwords->storeHash($user, $new);
        $this->audit('auth.password.changed', $user->id, null);
        return ['success' => true];
    }

    /** ADMIN — troca de e-mail (autorização checada no handler). */
    public function doAdminChangeEmail(): array
    {
        $app = App::i();
        $email = trim((string) $app->request->post('email', ''));
        $newEmail = strtolower(trim((string) $app->request->post('new_email', '')));

        $user = $this->resolveByEmail($email);
        if (!$user) {
            return ['success' => false, 'errors' => ['email' => [i::__('Usuário não encontrado.', 'local-auth')]]];
        }
        if (!Validator::email()->validate($newEmail)) {
            return ['success' => false, 'errors' => ['email' => [i::__('Informe um email válido.', 'local-auth')]]];
        }
        if ($this->resolveByEmail($newEmail)) {
            return ['success' => false, 'errors' => ['email' => [i::__('Este endereço de email já está em uso.', 'local-auth')]]];
        }

        $app->disableAccessControl();
        $user->email = $newEmail;
        $user->save(true);
        $app->em->flush();
        $app->enableAccessControl();

        return ['success' => true, 'new_email' => $newEmail];
    }

    /** Envio de e-mail (templates do módulo; hook para customização). */
    private function sendMail(string $to, string $subject, string $template, array $data): void
    {
        $app = App::i();
        try {
            $path = dirname(__DIR__, 1) . '/LocalAuth/views/emails/' . $template . '.html';
            $content = (new \Mustache_Engine())->render((string) file_get_contents($path), $data + [
                'baseUrl' => $app->getBaseUrl(),
                'urlSupportChat' => $this->localConfig['urlSupportChat'] ?? '',
                'urlSupportEmail' => $this->localConfig['urlSupportEmail'] ?? '',
                'urlSupportSite' => $this->localConfig['urlSupportSite'] ?? '',
                'textSupportSite' => $this->localConfig['textSupportSite'] ?? '',
                'urlImageToUseInEmails' => $this->getImageUrl(),
            ]);
            $app->applyHook('localAuth.emailSubject', [&$subject, $template]);
            $app->applyHook('localAuth.emailBody', [&$content, $template]);
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'] ?? '',
                'to' => $to,
                'subject' => $subject,
                'body' => $content,
            ]);
        } catch (\Throwable $e) {
            $app->log->error("[auth] sendMail({$template}) falhou: " . $e->getMessage());
        }
    }

    private function getImageUrl(): string
    {
        if (!empty($this->localConfig['urlImageToUseInEmails'])) {
            return (string) $this->localConfig['urlImageToUseInEmails'];
        }
        try {
            return \MapasCulturais\App::i()->view->asset('img/mail-image.png', false);
        } catch (\Throwable $e) {
            return '';
        }
    }
}
