<?php
$this->useOpportunityAPI();
$entity = $this->controller->requestedEntity;

$this->jsObject['config']['opportunityBasicInfo'] = [
    'date' => $entity::CONTINUOUS_FLOW_DATE,
];