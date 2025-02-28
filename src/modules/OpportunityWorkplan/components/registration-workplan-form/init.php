<?php

$this->addOpportunityPhasesToJs();
$this->jsObject['config']['registration-workplan-form']['parentRegistration'] = $this->controller->requestedEntity->firstPhase->id;
