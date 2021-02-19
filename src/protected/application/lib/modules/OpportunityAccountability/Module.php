<?php

namespace OpportunityAccountability;

use MapasCulturais\i;

class Module extends \MapasCulturais\Module
{
    function _init()
    {
    }

    function register()
    {
        $this->registerOpportunityMetadata('isAccountabilityPhase', [
            'label' => i::__('Indica se a oportunidade é uma fase de prestação de contas'),
            'type' => 'boolean',
            'default' => false
        ]);
    }
}
