<?php
$entity = $this->controller->requestedEntity;
$relatedOpportunities = $entity->getOpportunities();

function orderEntities($a, $b) {
    return $a->registrationTo <=> $b->registrationTo;
}

usort($relatedOpportunities, 'orderEntities');

$opportunities = [];

foreach($relatedOpportunities as $opportunity) {
    $opportunities[] = $opportunity->simplify("id,name,avatar,registrationFrom,registrationTo");
}

$this->jsObject['opportunityList']['opportunity'] = $opportunities;


