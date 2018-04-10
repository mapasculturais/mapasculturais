<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\i;

trait ControllerOpportunities {

    function GET_createOpportunity() {
        $app = App::i();
        
        $entity = $this->requestedEntity;
        
        $entity->checkPermission('@control');
        
        $opportunity_class = $entity->getOpportunityClassName();
        
        $opportunity = new $opportunity_class;
        
        $opportunity->name = i::__('Nova Oportunidade');
        $opportunity->type = 1;
        
        $opportunity->status = Entities\Opportunity::STATUS_DRAFT;
        
        $opportunity->ownerEntity = $entity;
             
        $opportunity->save();
        
        $definition = $app->getRegisteredEvaluationMethodBySlug($this->data['evaluationMethod']);
            
        $emconfig = new Entities\EvaluationMethodConfiguration;

        $emconfig->opportunity = $opportunity;

        $emconfig->type = $definition->slug;

        $emconfig->save(true);

        $app->redirect($opportunity->editUrl);
    }

}
