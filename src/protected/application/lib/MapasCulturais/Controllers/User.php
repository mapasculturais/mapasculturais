<?php
namespace MapasCulturais\Controllers;
use MapasCulturais\Controller;
use MapasCulturais\App;
/**
 * User Controller
 *
 * By default this controller is registered with the id 'user'.
 *
 */
class User extends Controller {
    function usesAPI(){
        return true;
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