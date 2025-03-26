<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use OpportunityWorkplan\Entities\Goal;

$goalsStatuses = Goal::getStatusesNames();

$this->jsObject['config']['goalsStatuses'] = $goalsStatuses;
