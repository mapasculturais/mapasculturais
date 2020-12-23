<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;

trait RegisterFunctions {
    function registerMetadata($entity_class, $key, $cfg) {
        $app = \MapasCulturais\App::i();
        $def = new \MapasCulturais\Definitions\Metadata($key, $cfg);
        return $app->registerMetadata($def, $entity_class);

    }

    function registerUserMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\User', $key, $cfg);
    }

    function registerEventMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Event', $key, $cfg);
    }

    function registerSpaceMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Space', $key, $cfg);
    }

    function registerAgentMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Agent', $key, $cfg);
    }

    function registerProjectMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Project', $key, $cfg);
    }

    function registerOpportunityMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Opportunity', $key, $cfg);
    }

    function registerRegistrationMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Registration', $key, $cfg);
    }

    function registerSealMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Seal', $key, $cfg);
    }
}
