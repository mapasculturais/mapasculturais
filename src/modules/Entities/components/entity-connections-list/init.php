<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$entity = $this->controller->requestedEntity;

if (!$entity || !method_exists($entity, 'getOpportunities')) {
    return;
}

if (!isset($this->jsObject['opportunityList'])) {
    $this->jsObject['opportunityList'] = [];
}

if (!empty($this->jsObject['opportunityList']['opportunity'])) {
    return;
}

$relatedOpportunities = $entity->getOpportunities();

usort($relatedOpportunities, fn($a, $b) => $a->registrationTo <=> $b->registrationTo);

$opportunities = [];

foreach ($relatedOpportunities as $opportunity) {
    $opportunities[] = $opportunity->simplify("id,name,avatar,registrationFrom,registrationTo,shortDescription,type,terms,owner");
}

$this->jsObject['opportunityList']['opportunity'] = $opportunities;
