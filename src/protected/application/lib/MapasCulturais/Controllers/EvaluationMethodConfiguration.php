<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * Space Controller
 *
 * By default this controller is registered with the id 'space'.
 *
 */
class EvaluationMethodConfiguration extends EntityController {
    use Traits\ControllerTypes,
        Traits\ControllerAgentRelation;

    public function GET_create() {
        App::i()->pass();
    }
    
    public function GET_delete() {
        App::i()->pass();
    }
    
    public function GET_edit() {
        App::i()->pass();
    }
    
    public function GET_index() {
        App::i()->pass();
    }
    
    public function GET_single() {
        App::i()->pass();
    }
        
    public function DELETE_single() {
        App::i()->pass();
    }
    
    protected function _setPermissionCacheUsers(){
        $app = App::i();
        
        $entity = $this->requestedEntity;

        $entity_fetch = $entity->fetch ? $entity->fetch : new \stdClass ;
        $entity_fetch_categories = $entity->fetchCategories ? $entity->fetchCategories : new \stdClass;

        $data_fetch = isset($this->data['fetch']) ? (object) $this->data['fetch'] : new \stdClass;
        $data_fetch_categories = isset($this->data['fetchCategories']) ? (object) $this->data['fetchCategories'] : new \stdClass;
        
        $user_ids = [];

        if($data_fetch){
            foreach($data_fetch as $id => $val){
                if(!isset($entity_fetch->$id) || $entity_fetch->$id != $val){
                    $user_ids[] = $id;
                }
            }

            foreach($entity_fetch as $id => $val){
                if(!isset($data_fetch->$id)){
                    $user_id[] = $id;
                }
            }
        }


        if($data_fetch_categories){
            foreach($data_fetch_categories as $id => $val){
                if(!isset($entity_fetch_categories->$id) || $entity_fetch_categories->$id != $val){
                    $user_ids[] = $id;
                }
            }

            foreach($entity_fetch_categories as $id => $val){
                if(!isset($data_fetch_categories->$id)){
                    $user_id[] = $id;
                }
            }
        }

        $user_ids = array_filter($user_ids, function($e){
            if(is_numeric($e)){
                return $e;
            }
        });
        
        $user_ids = array_unique($user_ids);
        
        if($user_ids){
            $app->permissionCacheUsersIds = $user_ids;
        } else {
            $app->skipPermissionCacheRecreation = true;
        }
    }
    
    function PATCH_single($data = null) {
        $this->_setPermissionCacheUsers();
        
        parent::PATCH_single();
    }
    
    
    function POST_single($data = null) {
        $this->_setPermissionCacheUsers();
        
        parent::POST_single();
    }
}
