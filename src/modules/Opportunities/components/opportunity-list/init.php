<?php
$entity = $this->controller->requestedEntity;
$relatedOpportunities = $entity->getOpportunities();

function orderEntities($a, $b) {
    return $a->registrationTo <=> $b->registrationTo;
}

usort($relatedOpportunities, 'orderEntities');


$this->jsObject['opportunityList']['opportunity'] = $relatedOpportunities;


