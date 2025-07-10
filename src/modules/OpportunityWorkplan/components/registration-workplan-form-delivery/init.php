<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use OpportunityWorkplan\Entities\Delivery;

$deliveriesStatuses = Delivery::getStatusesNames();

$this->jsObject['config']['deliveriesStatuses'] = $deliveriesStatuses;
