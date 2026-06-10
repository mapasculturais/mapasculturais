<?php

namespace UserManagement;

use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\Entities as MapasEntities;
use MapasCulturais\Definitions\Role;
use MapasCulturais\Entities\User;
use MapasCulturais\Exceptions\Halt;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\i;
use MapasCulturais\Utils;

class Module extends \MapasCulturais\Module {

    private const GLOBAL_EMAIL_CONFIG_FILE = 'platform-config/account-deletion-email.json';

    /**
     * Retorna o e-mail configurado explicitamente para solicitações de exclusão de conta.
     *
     * Prioriza o metadado do subsite atual; sem subsite, lê de PUBLIC_FILES.
     *
     * @return string|null
     */
    public static function getAccountDeletionRecipientEmail(): ?string
    {
        $app = App::i();
        $subsite = $app->getCurrentSubsite();

        if ($subsite) {
            $email = trim($subsite->email_exclusao_conta ?? '');
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return self::getGlobalAccountDeletionEmailFromFile();
    }

    /**
     * Retorna os destinatários da solicitação de exclusão de conta.
     *
     * Com e-mail configurado, envia somente para ele. Caso contrário, envia para
     * os administradores do subsite atual ou, sem subsite, para todos os usuários
     * com papel administrativo em qualquer nível (saasSuperAdmin, saasAdmin, superAdmin, admin).
     *
     * @return string[]
     */
    public static function getAccountDeletionRecipients(): array
    {
        $configured = self::getAccountDeletionRecipientEmail();
        if ($configured) {
            return [$configured];
        }

        $app = App::i();
        $subsite_id = $app->getCurrentSubsiteId();

        $admins = $subsite_id
            ? $app->repo('User')->getAdmins($subsite_id)
            : self::getAllPlatformAdminUsers();

        $emails = [];
        foreach ($admins as $admin) {
            $email = trim($admin->email ?? '');
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }

        return array_values(array_unique($emails));
    }

    /**
     * Usuários com qualquer papel administrativo, independentemente do subsite da role.
     *
     * @return User[]
     */
    private static function getAllPlatformAdminUsers(): array
    {
        $app = App::i();

        $dql = "
            SELECT DISTINCT u
            FROM MapasCulturais\Entities\User u
            JOIN u.roles r
            WITH r.name IN ('saasSuperAdmin', 'saasAdmin', 'superAdmin', 'admin')
        ";

        return $app->em->createQuery($dql)->getResult();
    }

    private static function getPublicFilesPath(): string
    {
        $path = env('PUBLIC_FILES_PATH', BASE_PATH . 'files');

        return rtrim($path, '/') . '/';
    }

    private static function getGlobalEmailConfigFilePath(): string
    {
        return self::getPublicFilesPath() . self::GLOBAL_EMAIL_CONFIG_FILE;
    }

    /**
     * Indica se o usuário logado pode alterar o e-mail de exclusão de conta.
     * Administradores do subsite atual (ou globais, sem subsite) têm permissão.
     *
     * @return bool
     */
    public static function canUserConfigureAccountDeletionEmail(): bool
    {
        $app = App::i();

        if ($app->user->is('guest')) {
            return false;
        }

        if ($app->user->is('saasSuperAdmin') || $app->user->is('saasAdmin')) {
            return true;
        }

        return $app->user->is('admin') || $app->user->is('superAdmin');
    }

    private static function getGlobalAccountDeletionEmailFromFile(): ?string
    {
        $file = self::getGlobalEmailConfigFilePath();
        if (!is_file($file)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data)) {
            return null;
        }

        $email = trim($data['email'] ?? '');
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        return null;
    }

    private static function saveGlobalAccountDeletionEmail(string $email): bool
    {
        $file = self::getGlobalEmailConfigFilePath();
        $dir = dirname($file);

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        $content = json_encode(['email' => $email], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($file, $content) !== false;
    }

    function _init() {
        $app = App::i();

        $app->hook('mapas.printJsObject:before', function () {
            $this->jsObject['EntitiesDescription']['system-role'] = Entities\SystemRole::getPropertiesMetadata();
            $this->jsObject['EntitiesDescription']['role'] = \MapasCulturais\Entities\Role::getPropertiesMetadata();
        });

        /**
         * Adiciona a descrição da entiade ao jsObject
         */
        $app->hook('view.render(<<*>>):before', function () use($app) {
            $subsite_query = new ApiQuery(MapasEntities\Subsite::class, ['@select' => 'id,name']);
            $this->jsObject['subsites'] = array_merge([], $subsite_query->find());

            $permission_labels = [
                '@control' => i::__('*controlar'),
                'create' => i::__('criar'),
                'modify' => i::__('modificar'),
                'remove' => i::__('remover'),
                'deleteAccount' => i::__('remover conta'),
                'destroy' => i::__('remover permanentemente'),
                'evaluate' => i::__('avaliar'),
                'view' => i::__('visualizar'),
                'send' => i::__('enviar'),
                'changeOwner' => i::__('mudar proprietário'),
                'publish' => i::__('publicar'),
                'unpublish' => i::__('despublicar'),
                'archive' => i::__('arquivar'),
                'unarchive' => i::__('desarquivar'),
                'viewPrivateData' => i::__('visualizar dados privados'),
                'viewPrivateFiles' => i::__('visualizar arquivos privados'),

                'createAgentRelation' => i::__('relacionar agentes'),
                'removeAgentRelation' => i::__('remover agentes relacionados'),

                'createSpaceRelation' => i::__('relacionar espaços'),
                'removeSpaceRelation' => i::__('remover espaços relacionados'),

                'createAgentRelationWithControl' => i::__('adicionar adiministradores'),
                'removeAgentRelationWithControl' => i::__('remover administradores'),

                'createSealRelation' => i::__('aplicar selos'),
                'removeSealRelation' => i::__('remover selos'),

                'createEvents' => i::__('criar eventos'),

                'register' => i::__('inscrever-se'),

                'modifyRegistrationFields' => i::__('modificar campos do formulário'),
                'publishRegistrations' => i::__('publicar resultado'),
                'sendUserEvaluations' => i::__('enviar avaliações do usuário'),
                'viewEvaluations' => i::__('visualizar avaliações'),
                'viewUserEvaluation' => i::__('visualizar avaliação'),

                'reopenValuerEvaluations' => i::__('reabrir avaliações dos avaliadores'),
                'evaluateRegistrations' => i::__('avaliar inscrições'),

                'viewConsolidatedResult' => i::__('visualizar resultado'),
                'changeStatus' => i::__('modificar status'),
                'requestEventRelation' => i::__('solicitar evento relacionado'),
            ];

            $permission_descriptions = [
                'requestEventRelation' => 'O usuário poderá solicitar que o evento que está criando/editando seja relacionado aos projetos',
                '@control' => 'Usuário com controle total'
            ];
            
            $app->applyHook('module(UserManagement).permissionsLabels', [&$permission_labels]);
            $app->applyHook('module(UserManagement).permissionsDescriptions', [&$permission_descriptions]);

            $entity_classes = [
                'user' => MapasEntities\User::class,
                'agent' => MapasEntities\Agent::class,
                'space' => MapasEntities\Space::class,
                'event' => MapasEntities\Event::class,
                'project' => MapasEntities\Project::class,
                'opportunity' => MapasEntities\Opportunity::class,
                'registration' => MapasEntities\Registration::class,
                'seal' => MapasEntities\Seal::class,
            ];

            $result = [];

            foreach ($entity_classes as $slug => $class) {
                $private_entity = $class::isPrivateEntity();
                $rs = [];
                foreach ($class::getPermissionsList() as $permission) {
                    if ($permission == 'view' && !$private_entity) {
                        continue;
                    }
                    $rs[$permission] = [
                        'permission' => $permission,
                        'label' => $permission_labels[$permission] ?? '',
                        'description' => $permission_descriptions[$permission] ?? '',
                    ];
                }
                $result[$slug] = [];

                // adiciona as permissões na ordem definida no array $permission_labels
                foreach (array_keys($permission_labels) as $permission) {
                    if (isset($rs[$permission])) {
                        $result[$slug][] = $rs[$permission];
                    }
                }

                // se alguma permissão não estava na lista, adiciona
                if (count($result[$slug]) < count($rs)) {
                    foreach ($rs as $permission) {
                        if (!$permission['label']) {
                            $result[$slug][] = $permission;
                        }
                    }
                }
            }

            $this->jsObject['EntityPermissionsList'] = $result;

            $this->jsObject['accountDeletion'] = [
                'recipientEmail' => self::getAccountDeletionRecipientEmail(),
                'canConfigure' => self::canUserConfigureAccountDeletionEmail(),
                'hasSubsite' => $app->getCurrentSubsite() !== null,
            ];
        });

        /**
         * Faz JOIN com a tabela Agent para poder fitrar por nome nas keyowrds
         */
        $app->hook('repo(User).getIdsByKeywordDQL.join', function (&$joins, $keyword) {
            $joins .= "
                LEFT JOIN 
                    MapasCulturais\\Entities\\Agent a 
                WITH 
                    e.profile = a.id

                LEFT JOIN 
                    a.__metadata nomeCompleto 
                WITH nomeCompleto.key = 'nomeCompleto'
                
                LEFT JOIN 
                    a.__metadata nomeSocial 
                WITH nomeSocial.key = 'nomeSocial'

                LEFT JOIN
                    MapasCulturais\\Entities\\Agent user_agents
                WITH
                    user_agents.user = e.id
                
                LEFT JOIN 
                    user_agents.__metadata user_agent_nomeCompleto 
                WITH user_agent_nomeCompleto.key = 'nomeCompleto'
                
                LEFT JOIN 
                    user_agents.__metadata user_agent_nomeSocial 
                WITH user_agent_nomeSocial.key = 'nomeSocial'
                
                ";

            if (strlen(preg_replace("/\D/", '', $keyword)) >= 11) {
                $joins .= "
                    LEFT JOIN 
                        a.__metadata doc 
                    WITH doc.key = 'documento'
                    
                    LEFT JOIN 
                    user_agents.__metadata user_agent_doc 
                    WITH user_agent_doc.key = 'documento'
                    
                    LEFT JOIN 
                        a.__metadata cpf 
                    WITH cpf.key = 'cpf'

                    LEFT JOIN 
                    user_agents.__metadata user_agent_cpf 
                    WITH user_agent_cpf.key = 'cpf'

                    LEFT JOIN 
                        a.__metadata cnpj 
                    WITH cnpj.key = 'cnpj'

                    LEFT JOIN 
                    user_agents.__metadata user_agent_cnpj 
                    WITH user_agent_cnpj.key = 'cnpj'
                    
                ";
            }
        });

        /**
         * Filtra usuários por palavras chaves na view user-management
         */
        $app->hook('repo(User).getIdsByKeywordDQL.where', function (&$where, $keyword, $alias) {
            $where .= " 
            (
                unaccent(lower(e.email)) LIKE unaccent(lower(:{$alias})) OR 
                unaccent(lower(a.name)) LIKE unaccent(lower(:{$alias})) OR
                unaccent(lower(nomeCompleto.value)) LIKE unaccent(lower(:{$alias})) OR
                unaccent(lower(nomeSocial.value)) LIKE unaccent(lower(:{$alias})) OR

                unaccent(lower(user_agents.name)) LIKE unaccent(lower(:{$alias})) OR
                unaccent(lower(user_agent_nomeCompleto.value)) LIKE unaccent(lower(:{$alias})) OR
                unaccent(lower(user_agent_nomeSocial.value)) LIKE unaccent(lower(:{$alias}))
            )";

            $doc = preg_replace("/\D/", '', $keyword);
            if (strlen($doc) >= 11) {
                $formated_doc = Utils::formatCnpjCpf($doc);
                $where .= " OR doc.value = '{$doc}' OR doc.value = '{$formated_doc}'";
                $where .= " OR user_agent_doc.value = '{$doc}' OR user_agent_doc.value = '{$formated_doc}'";
                $where .= " OR cpf.value = '{$doc}' OR cpf.value = '{$formated_doc}'";
                $where .= " OR user_agent_cpf.value = '{$doc}' OR user_agent_cpf.value = '{$formated_doc}'";
                $where .= " OR cnpj.value = '{$doc}' OR cnpj.value = '{$formated_doc}'";
                $where .= " OR user_agent_cnpj.value = '{$doc}' OR user_agent_cnpj.value = '{$formated_doc}'";
            }
        });

        $app->hook('panel.nav', function (&$group) use ($app) {
            $group['admin']['items'][] = [
                'route' => 'panel/user-management',
                'icon' => 'user-config',
                'label' => i::__('Gestão de usuários'),
                'condition' => function () use ($app) {
                    return $app->user->is('admin');
                }
            ];

            $group['admin']['items'][] = [
                'route' => 'panel/system-roles',
                'icon' => 'role',
                'label' => i::__('Funções de usuários'),
                'condition' => function () use ($app) {
                    return $app->user->is('saasAdmin');
                }
            ];
        });

        /**
         * Página para gerenciamento de roles no painel
         */
        $app->hook('GET(panel.system-roles)', function () use ($app) {
            $this->requireAuthentication();

            if (!$app->user->is('saasAdmin')) {
                throw new PermissionDenied($app->user, null, i::__('Gerenciar Funções de Usuário'));
            }

            $this->render('system-roles');
        });


        /**
         * Página para gerenciamento de usuários
         */
        $app->hook('GET(panel.user-management)', function () use ($app) {
            $this->requireAuthentication();

            if (!$app->user->is('admin')) {
                throw new PermissionDenied($app->user, null, i::__('Gerenciar Usuários'));
            }

            $this->render('user-management');
        });

        /**
         * Salva o e-mail destinatário das solicitações de exclusão de conta.
         *
         * Se houver subsite atual, salva no metadata do subsite.
         * Caso contrário, persiste em PUBLIC_FILES (platform-config/account-deletion-email.json).
         */
        $app->hook('POST(panel.setAccountDeletionEmail)', function () use ($app) {
            /** @var \MapasCulturais\Controllers\Panel $this */
            $this->requireAuthentication();

            if (!self::canUserConfigureAccountDeletionEmail()) {
                $this->errorJson(i::__('Permissão negada.'), 403);
                return;
            }

            $email = isset($this->data['email']) ? trim($this->data['email']) : '';
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errorJson(i::__('O e-mail informado é inválido.'), 400);
                return;
            }

            $subsite = $app->getCurrentSubsite();
            if ($subsite) {
                $subsite->email_exclusao_conta = $email;
                $subsite->save(true);
                $this->json(['success' => true, 'email' => $email, 'scope' => 'subsite']);
                return;
            }

            if (!self::saveGlobalAccountDeletionEmail($email)) {
                $this->errorJson(i::__('Não foi possível salvar a configuração.'), 500);
                return;
            }

            $this->json(['success' => true, 'email' => $email, 'scope' => 'global']);
        });

        /**
         * Processa solicitação de exclusão de conta (LGPD).
         */
        $app->hook('POST(user.requestAccountDeletion)', function () use ($app) {
            /** @var \MapasCulturais\Controllers\User $this */
            $this->requireAuthentication();

            $data = $this->data;
            $message = isset($data['message']) ? trim($data['message']) : '';
            $sendCopy = !empty($data['sendCopy']);
            $copyEmail = isset($data['copyEmail']) ? trim($data['copyEmail']) : '';

            if ($message === '') {
                $this->errorJson(i::__('A mensagem de solicitação é obrigatória.'), 400);
                return;
            }

            $recipients = self::getAccountDeletionRecipients();
            if (!$recipients) {
                $this->errorJson(i::__('Não foi possível identificar destinatários para a solicitação de exclusão de conta.'), 400);
                return;
            }

            $user = $app->user;
            $profile = $user->profile;

            if ($sendCopy && $copyEmail === '') {
                $copyEmail = trim($user->email);
            }

            $params = [
                'siteName' => $app->siteName,
                'baseUrl' => $app->getBaseUrl(),
                'userName' => $profile ? $profile->name : $user->email,
                'userEmail' => $user->email,
                'userId' => $user->id,
                'message' => nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')),
            ];

            $rendered = $app->renderMailerTemplate('request_account_deletion', $params);

            $emailParams = [
                'from' => $app->config['mailer.from'],
                'to' => $recipients,
                'subject' => $rendered['title'],
                'body' => $rendered['body'],
            ];

            try {
                if (!$app->createAndSendMailMessage($emailParams)) {
                    $this->errorJson(i::__('Não foi possível enviar a solicitação. Tente novamente mais tarde.'), 500);
                    return;
                }

                if ($sendCopy && $copyEmail !== '' && filter_var($copyEmail, FILTER_VALIDATE_EMAIL)) {
                    $app->createAndSendMailMessage([
                        'from' => $app->config['mailer.from'],
                        'to' => $copyEmail,
                        'subject' => $rendered['title'],
                        'body' => $rendered['body'],
                    ]);
                }
            } catch (Halt $e) {
                throw $e;
            } catch (\Throwable $e) {
                $app->log->error($e->getMessage());
                $this->errorJson(i::__('Não foi possível enviar a solicitação. Tente novamente mais tarde.'), 500);
                return;
            }

            $this->json(['success' => true, 'message' => i::__('Solicitação enviada com sucesso.')]);
        });

        /**
         * Página para gerenciamento de usuários
         */
        $app->hook('GET(panel.user-detail)', function () use ($app) {
            /** @var \MapasCulturais\Controllers\Panel $this */
            $this->requireAuthentication();
            $user = $app->repo('User')->find($this->data['id'] ?? -1);
            if (!$user) {
                $app->pass();
            }

            if (!$app->user->is('admin')) {
                throw new PermissionDenied($app->user, null, i::__('Gerenciar Usuários'));
            }

            $app->view->addRequestedEntityToJs(User::class, $this->data['id']);
            $this->render('user-detail');
        });

        /**
         * Página de Conta e privacidade
         */
        $app->hook('GET(panel.my-account)', function () use ($app) {
            /** @var \MapasCulturais\Controllers\Panel $this */
            $this->requireAuthentication();
            $app->view->addRequestedEntityToJs(User::class, $app->user->id);
            $this->render('my-account');
        });
        
        /**
         * Página de apps
         */
        $app->hook('GET(panel.my-app)', function () use ($app) {
            /** @var \MapasCulturais\Controllers\Panel $this */
            $this->requireAuthentication();
            $app->view->addRequestedEntityToJs(User::class, $app->user->id);
            $this->render('my-app');
        });

        /**
         * Atualiza o ENUM de object_types adicionando a classe UserManagement\Entities\SystemRole
         */
        $app->hook('doctrine.emum(object_type).values', function (&$values) {
            $values['SystemRole'] = Entities\SystemRole::class;
        });

        $app->hook('app.init:after', function () {
            $this->registerController('system-role', Controllers\SystemRole::class);
            $this->registerController('role', Controllers\Role::class);

            $roles = $this->repo(Entities\SystemRole::class)->findBy(['status' => 1]);

            foreach ($roles as $role) {
                $definition = new Role($role->slug, $role->name, $role->name, $role->subsiteContext, function ($user) {
                    return $user->is('saasAdmin');
                });

                $this->registerRole($definition);

                foreach ($role->permissions as $permission) {
                    $this->hook("can($permission)", function ($user, &$result) use ($role) {
                        if ($user->is($role->slug)) {
                            $result = true;
                        }
                    });
                }
            }
        });
    }

    function register()
    {
    }
}
