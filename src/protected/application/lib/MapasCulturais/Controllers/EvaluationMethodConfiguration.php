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
        }
        
    function __construct()
    {
        $this->entityClassName = EvaluationMethodConfigurationEntity::class;
    }

    function POST_index($data = null) {
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
}
