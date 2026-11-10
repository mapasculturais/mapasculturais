<?php
$entity = $this->controller->requestedEntity;
$relatedOpportunities = $entity->getOpportunities();

usort($relatedOpportunities, fn($a, $b) => $a->registrationTo <=> $b->registrationTo);

$opportunities = [];

foreach($relatedOpportunities as $opportunity) {
    $opportunities[] = $opportunity->simplify("id,name,avatar,registrationFrom,registrationTo,shortDescription,type,terms,owner");
}

$this->jsObject['opportunityList']['opportunity'] = $opportunities;
