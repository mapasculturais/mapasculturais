<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

/** @todo avaliar se a linha abaixo é necessária */
$this->addOpportunityPhasesToJs($this->controller->requestedEntity);
$this->addRegistrationPhasesToJs();
$this->jsObject['config']['registration-workplan-form']['parentRegistration'] = $this->controller->requestedEntity->firstPhase->id;
