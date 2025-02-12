<?php

use OpportunityWorkplan\Entities\Delivery;
use OpportunityWorkplan\Entities\Goal;
use OpportunityWorkplan\Entities\Workplan;

$this->jsObject['EntitiesDescription']['workplan'] = Workplan::getPropertiesMetadata();
$this->jsObject['EntitiesDescription']['workplan']['goal'] = Goal::getPropertiesMetadata();
$this->jsObject['EntitiesDescription']['workplan']['goal']['delivery'] = Delivery::getPropertiesMetadata();