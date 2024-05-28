<?php

namespace UserManagement;

use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\Entities as MapasEntities;
use MapasCulturais\Definitions\Role;
use MapasCulturais\Entities\User;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\i;
use MapasCulturais\Utils;

class Module extends \MapasCulturais\Module {
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
                    a._children children

                LEFT JOIN 
                    children.__metadata child_nomeCompleto 
                WITH child_nomeCompleto.key = 'nomeCompleto'
                
                LEFT JOIN 
                    children.__metadata child_nomeSocial 

                WITH child_nomeSocial.key = 'nomeSocial'
                
                
                ";

            if (strlen(preg_replace("/\D/", '', $keyword)) >= 11) {
                $joins .= "
                    LEFT JOIN 
                        a.__metadata doc 
                    WITH doc.key = 'documento'

                    LEFT JOIN 
                    children.__metadata child_doc 
                    WITH child_doc.key = 'documento'
                    
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

                unaccent(lower(children.name)) LIKE unaccent(lower(:{$alias})) OR
                unaccent(lower(child_nomeCompleto.value)) LIKE unaccent(lower(:{$alias})) OR
                unaccent(lower(child_nomeSocial.value)) LIKE unaccent(lower(:{$alias}))
            )";

            $doc = preg_replace("/\D/", '', $keyword);
            if (strlen($doc) >= 11) {
                $formated_doc = Utils::formatCnpjCpf($doc);
                $where .= " OR doc.value = '{$doc}' OR doc.value = '{$formated_doc}'";
                $where .= " OR child_doc.value = '{$doc}' OR child_doc.value = '{$formated_doc}'";
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
         * Página para deletar conta de usuário
         */
        $app->hook('GET(panel.delete-account)', function () use ($app) {
            /** @var \MapasCulturais\Controllers\Panel $this */
            $this->requireAuthentication();

            if (!$app->user->is('admin')) {
                throw new PermissionDenied($app->user, null, i::__('Gerenciar Usuários'));
            }

            $this->render('delete-account');
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
