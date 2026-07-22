<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\ApiQuery;
use MapasCulturais\Controller;
use MapasCulturais\Entities;
use MapasCulturais\App;
use MapasCulturais\Entities\User as UserEntity;
use MapasCulturais\i;
use MapasCulturais\Traits;

/**
 * Controlador de Usuários
 *
 * Este controlador gerencia as operações relacionadas a entidades User (Usuários)
 * no sistema Mapas Culturais. Por padrão, este controlador é registrado com o ID 'user'.
 *
 * O controlador de usuários possui restrições de acesso mais rigorosas, pois lida
 * com informações sensíveis de autenticação e perfis de usuários.
 * 
 * @property-read Entities\User $requestedEntity Entidade do usuário solicitada
 *
 * @package MapasCulturais\Controllers
 */
class User extends Controller {
    use Traits\ControllerSoftDelete;
    use Traits\ControllerEntity;
    use Traits\ControllerEntityActions {
        POST_index as __DISABLE_POST_INDEX__;
    }
    use Traits\ControllerAPI {
            API_find as __API_find;
            API_findOne as __API_findOne;
    }
    
    /**
     * Construtor do controlador de usuários.
     *
     * Define o nome da classe da entidade como \MapasCulturais\Entities\User.
     */
    function __construct()
    {
        $this->entityClassName = 'MapasCulturais\\Entities\\User';
    }

    /**
     * Impede a criação de usuários via POST.
     *
     * Este método redireciona a requisição, pois usuários não podem ser criados
     * diretamente através do controlador (são criados via autenticação).
     *
     * @return void
     */
    function POST_index() {
        App::i()->pass();
    }

    /**
     * Retorna a entidade do usuário solicitada na requisição atual.
     *
     * Busca o usuário pelo ID especificado nos dados da URL.
     *
     * @return UserEntity|null Entidade do usuário ou null se não encontrado
     */
    function getRequestedEntity(): ?UserEntity {
        $app = App::i();
        if($id = $this->urlData['id']) {
            return $app->repo('User')->find($id);
        } else {
            return null;
        }
    }

    /**
     * Exclui um usuário.
     *
     * Esta ação requer autenticação e permissão para excluir o usuário solicitado.
     * Retorna os dados do usuário excluído em formato JSON.
     *
     * @return void
     */
    function DELETE_single(){

        $app = App::i();
        $entity = $this->requestedEntity;
        
        if(!$entity) {
            $app->pass();
        }

        $entity->delete(true);

        $this->json($entity);

    }

    /**
     * Busca usuários através da API.
     *
     * Esta ação possui restrições de acesso: apenas administradores podem buscar usuários,
     * exceto durante o processo de autenticação fake (para testes).
     * Permite filtrar usuários por papéis (roles) usando o parâmetro @roles.
     *
     * @return void
     */
    function API_find()
    {
        $app = App::i();
        if (!$app->user->is('admin')) {
            $is_fake_authentication = $app->auth instanceof \MapasCulturais\AuthProviders\Fake;
            
            $is_fake_auth_request = str_starts_with($app->request->getHeaderLine('referer'), $app->createUrl('auth', 'index'));
            
            if(!$is_fake_authentication || !$is_fake_auth_request) {
                $this->errorJson(i::__('Permissão negada', 403));
            }
        }

        if($roles = $this->getData['@roles'] ?? null) {
            $app->hook('API.query(user)', function(ApiQuery $query) use($roles) {
                $roles_query = new ApiQuery(Entities\Role::class, ['name' => "IN({$roles})"]);
                $query->addFilterByApiQuery($roles_query, 'userId', 'id');
            });

        }

        $app->hook('API.find(user).params', function(&$api_params) {
            unset($api_params['@roles']);
        });
        
        $this->__API_find();
    }

    /**
     * Busca um único usuário através da API.
     *
     * Esta ação requer privilégios de administrador.
     *
     * @return void
     */
    function API_findOne()
    {
        $app = App::i();
        if (!$app->user->is('admin')) {
            $this->errorJson(i::__('Permissão negada', 403));
        }

        $this->__API_find();
    }
    
    /**
     * Obtém o ID de um usuário pelo seu authUid (identificador de autenticação).
     *
     * Útil para integrações com sistemas externos de autenticação.
     *
     * @return void
     */
    function API_getId(){
        $app = App::i();
        if(!isset($this->data['authUid'])){
            $app->pass();
        }else{
            $auth_uid = $this->data['authUid'];
            $user = $app->repo('User')->findOneBy([
                'authUid' => $auth_uid
            ]);
            
            if($user){
                $this->json($user->id);
            }else{
                $this->json(null);
            }
        }
    }

    /**
     * Obtém os agentes que o usuário tem permissão de controle.
     *
     * Retorna uma lista de agentes onde o usuário possui permissões de controle.
     *
     * @return void
     */
    public function GET_relatedsAgentsControl() { 
        //$this->requireAuthentication();
        $app = App::i();
        if(!isset($this->getData['userId'])) {
            $app->pass();
        }
        $user = $app->repo('User')->find($this->getData['userId']);
        $this->json($user->getHasControlAgents());
    }

    /**
     * Obtém os espaços que o usuário tem permissão de controle.
     *
     * Retorna uma lista de espaços onde o usuário possui permissões de controle.
     *
     * @return void
     */
    public function GET_relatedsSpacesControl() { 
        //$this->requireAuthentication();
        $app = App::i();
        if(!isset($this->getData['userId'])) {
            $app->pass();
        }
        $user = $app->repo('User')->find($this->getData['userId']);
        $this->json($user->getHasControlSpaces());
    }

    /**
     * Obtém os eventos associados ao usuário.
     *
     * Retorna uma lista de eventos onde o usuário está relacionado.
     *
     * @return void
     */
    public function GET_events() {
        //$this->requireAuthentication();
        $app = App::i();
        if(!isset($this->getData['userId'])) {
            $app->pass();
        }
        $user = $app->repo('User')->find($this->getData['userId']);
        $this->json($user->getEvents( ));
    }

    /**
     * Obtém os eventos que o usuário tem permissão de controle.
     *
     * Retorna uma lista de eventos onde o usuário possui permissões de controle.
     *
     * @return void
     */
    public function GET_relatedsEventsControl() {
        //$this->requireAuthentication();
        $app = App::i();
        if(!isset($this->getData['userId'])) {
            $app->pass();
        }
        $user = $app->repo('User')->find($this->getData['userId']);
        $this->json($user->getHasControlEvents( ));
    }

    /**
     * Obtém o histórico de papéis (roles) do usuário.
     *
     * Retorna o histórico de atribuição e remoção de papéis do usuário.
     *
     * @return void
     */
    public function GET_history() {
        //$this->requireAuthentication();
        $app = App::i();
        if(!isset($this->getData['userId'])) {
            $app->pass();
        }
        $roles = $app->repo('User')->getHistory($this->getData['userId']);
        $this->json($roles);
    }
}