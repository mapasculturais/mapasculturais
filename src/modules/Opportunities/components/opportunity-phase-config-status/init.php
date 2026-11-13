<?php

use MapasCulturais\Entities\Opportunity;

$this->jsObject['config']['opportunityPhaseConfigStatus'] = [
    'appealHiddenStatuses' => Opportunity::getAppealPhaseHiddenStatuses(),
];
