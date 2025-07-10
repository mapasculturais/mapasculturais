<?php

/** @todo avaliar se a linha abaixo é necessária */
$this->addOpportunityPhasesToJs();
$this->addRegistrationPhasesToJs();
$this->jsObject['config']['registration-workplan-form']['parentRegistration'] = $this->controller->requestedEntity->firstPhase->id;
