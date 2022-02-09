<?php

namespace UserManagement;

use MapasCulturais\App;
use MapasCulturais\Entities as MapasEntities;
use MapasCulturais\Definitions\Role;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module {
    function _init() {
        $app = App::i();

        /**
         * Adiciona a descrição da entiade ao jsObject
         */
        $app->hook('view.render(<<*>>):before', function (){
            $this->jsObject['EntitiesDescription']['system-role'] = Entities\SystemRole::getPropertiesMetadata();

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
                'requestEventRelation' => 'O usuário poderá solicitar que o evento que está criando/editando seja relacionado aos projetos'
            ];
            
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
                    if($permission == 'view' && !$private_entity) {
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
                foreach(array_keys($permission_labels) as $permission) {
                    if(isset($rs[$permission])) {
                        $result[$slug][] = $rs[$permission];
                    }
                }

                // se alguma permissão não estava na lista, adiciona
                if(count($result[$slug]) < count($rs)) {
                    foreach($rs as $permission) {
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
        $app->hook('repo(User).getIdsByKeywordDQL.join', function(&$joins, $keyword){
            $joins .= "
                LEFT JOIN 
                    MapasCulturais\\Entities\\Agent a 
                WITH 
                    e.profile = a.id";
        });

        /**
         * Filtra usuários por palavras chaves na view user-management
         */
        $app->hook('repo(User).getIdsByKeywordDQL.where', function(&$where, $keyword){
            $where .= " (unaccent(lower(e.email)) LIKE unaccent(lower(:keyword)) OR unaccent(lower(a.name)) LIKE unaccent(lower(:keyword)))";
        });

        /**
         * Página para gerenciamento de roles no painel
         */
        $app->hook('GET(panel.system-roles)', function() use($app) {
            $this->requireAuthentication();

            $this->render('system-roles');
        });

        /**
         * Página para gerenciamento de usuários
         */
        $app->hook('GET(panel.user-management)', function() use($app) {
            $this->requireAuthentication();

            $this->render('user-management');
        });

        /**
         * Atualiza o ENUM de object_types adicionando a classe UserManagement\Entities\SystemRole
         */
        $app->hook('doctrine.emum(object_type).values', function(&$values) {
            $values['SystemRole'] = Entities\SystemRole::class;
        });

        $app->hook('app.init:after', function () {
            $this->registerController('system-role', Controllers\SystemRole::class);
            $this->registerController('user-management', Controllers\UserManagement::class);

            $roles = $this->repo(Entities\SystemRole::class)->findBy(['status' => 1]);
            
            foreach($roles as $role) {
                $definition = new Role($role->slug, $role->name, $role->name, $role->subsiteContext, function ($user) {
                    return $user->is('saasAdmin');
                });
    
                $this->registerRole($definition);
    
                foreach ($role->permissions as $permission) {
                    $this->hook("can($permission)", function ($user, &$result) use ($role) {
                        if($user->is($role->slug)) {
                            $result = true;
                        }
                    });
                }
            }
        });
    }

    function register() {        
    
    }
}
