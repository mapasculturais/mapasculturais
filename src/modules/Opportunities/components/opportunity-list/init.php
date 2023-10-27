<?php
$entity = $this->controller->requestedEntity;
$relatedOpportunities = $entity->getOpportunities();

$this->jsObject['opportunityList']['opportunity'] = $relatedOpportunities;


