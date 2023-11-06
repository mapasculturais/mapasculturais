<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Controller;
use MapasCulturais\Entities\EvaluationMethodConfiguration as EvaluationMethodConfigurationEntity;
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
class EvaluationMethodConfiguration extends Controller {
    use Traits\ControllerTypes,
        Traits\ControllerAgentRelation,
        Traits\ControllerEntity,
        Traits\ControllerEntityActions {
            Traits\ControllerEntityActions::POST_index as _POST_index;
            Traits\ControllerEntityActions::POST_single as _POST_single;
            Traits\ControllerEntityActions::PATCH_single as _PATCH_single;
        }
        
    function __construct()
    {
        $this->entityClassName = EvaluationMethodConfigurationEntity::class;
    }

    function POST_index($data = null) {
        if(isset($this->data['opportunity'])){
            $requested_entity = $this->getRequestedEntity();
            $requested_entity->opportunity = $this->data['opportunity'];
        }
        $this->_POST_index();
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
    
    function PATCH_single($data = null) {
        $this->_setPermissionCacheUsers();
        $this->_PATCH_single();
    }
    
    function POST_single($data = null) {
        $this->_setPermissionCacheUsers();
        $this->_POST_single();
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
}
