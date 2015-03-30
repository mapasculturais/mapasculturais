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
}