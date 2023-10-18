<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

// use MapasCulturais\Entities\EvaluationMethodConfiguration;

/**
 * Space Controller
 *
 * By default this controller is registered with the id 'space'.
 * 
 * @property \MapasCulturais\Entities\EvaluationMethodConfiguration $requestedEntity
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
            
        $user_ids = [];

        // pega os ids dos usuários que tiveram a configuração de distribuição pelo número dinal da inscrição alterada
        $entity_fetch = $entity->fetch ? $entity->fetch : new \stdClass ;
        $data_fetch = !empty($this->data['fetch']) ? (object) $this->data['fetch'] : new \stdClass;
        if($data_fetch){
            foreach($data_fetch as $id => $val){
                if(!is_numeric($id)) {
                    continue;
                }
                
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
        
        
        // pega os ids dos usuários que tiveram a configuração de categoria alterada
        $entity_fetch_categories = $entity->fetchCategories ? $entity->fetchCategories : new \stdClass;
        $data_fetch_categories = isset($this->data['fetchCategories']) ? (object) $this->data['fetchCategories'] : new \stdClass;
        if($data_fetch_categories){
            foreach($data_fetch_categories as $id => $val){
                if(!is_numeric($id)) {
                    continue;
                }

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

        if($user_ids) {
            $users = $app->repo('User')->findBy(['id' => $user_ids]);
            $entity->enqueueToPCacheRecreation($users);
        }

        $entity->__skipQueuingPCacheRecreation = true;
    }
    
    function PATCH_single($data = null) {
        $this->_setPermissionCacheUsers();
        
        parent::PATCH_single();
    }
    
    
    function POST_single($data = null) {
        $this->_setPermissionCacheUsers();
        
        parent::POST_single();
    }

    protected function _getValuerAgentRelation() {
        $this->requireAuthentication();

        $app = App::i();
        
        $entity = $this->requestedEntity;
        $relation = $app->repo('EvaluationMethodConfigurationAgentRelation')->find($this->data['relationId']);

        if(!$entity || !$relation){
            $app->pass();
        }

        return $relation;
    }

    function POST_reopenValuerEvaluations(){
        $relation = $this->_getValuerAgentRelation();

        $relation->reopen(true);

        $this->_finishRequest($relation);
    }

    function POST_disableValuer() {
        $relation = $this->_getValuerAgentRelation();

        $relation->disable(true);

        $this->_finishRequest($relation);
    }

    function POST_enableValuer() {
        $relation = $this->_getValuerAgentRelation();

        $relation->enable(true);

        $this->_finishRequest($relation);
    }
}
