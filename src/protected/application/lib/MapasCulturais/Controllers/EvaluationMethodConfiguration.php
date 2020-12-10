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
