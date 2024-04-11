<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\ApiQuery;
use MapasCulturais\Controller;
use MapasCulturais\Entities;
use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Traits;
/**
 * User Controller
 *
 * By default this controller is registered with the id 'user'.
 * 
 * @property-read Entities\User $requestedEntity;
 *
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
    
    function __construct()
    {
        $this->entityClassName = 'MapasCulturais\\Entities\\User';
    }

    function POST_index() {
        App::i()->pass();
    }

    function getRequestedEntity() {
        $app = App::i();
        if($id = $this->urlData['id']) {
            return $app->repo('User')->find($id);
        } else {
            return null;
        }
    }

    function DELETE_single(){

        $app = App::i();
        $entity = $this->requestedEntity;
        
        if(!$entity) {
            $app->pass();
        }

        $entity->delete(true);

        $this->json($entity);

    }

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

    function API_findOne()
    {
        $app = App::i();
        if (!$app->user->is('admin')) {
            $this->errorJson(i::__('Permissão negada', 403));
        }

        $this->__API_find();
    }
    
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

    public function GET_relatedsAgentsControl() { 
        //$this->requireAuthentication();
        $app = App::i();
        if(!isset($this->getData['userId'])) {
            $app->pass();
        }
        $user = $app->repo('User')->find($this->getData['userId']);
        $this->json($user->getHasControlAgents());
    }

    public function GET_relatedsSpacesControl() { 
        //$this->requireAuthentication();
        $app = App::i();
        if(!isset($this->getData['userId'])) {
            $app->pass();
        }
        $user = $app->repo('User')->find($this->getData['userId']);
        $this->json($user->getHasControlSpaces());
    }

    public function GET_events() {
        //$this->requireAuthentication();
        $app = App::i();
        if(!isset($this->getData['userId'])) {
            $app->pass();
        }
        $user = $app->repo('User')->find($this->getData['userId']);
        $this->json($user->getEvents( ));
    }

    public function GET_relatedsEventsControl() {
        //$this->requireAuthentication();
        $app = App::i();
        if(!isset($this->getData['userId'])) {
            $app->pass();
        }
        $user = $app->repo('User')->find($this->getData['userId']);
        $this->json($user->getHasControlEvents( ));
    }

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