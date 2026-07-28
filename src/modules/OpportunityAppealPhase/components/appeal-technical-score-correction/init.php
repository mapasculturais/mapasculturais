<?php

use MapasCulturais\Entities\Registration;

/** @var Registration $entity */
$entity = $this->controller->requestedEntity;
if (!$entity instanceof Registration
    || !$entity->opportunity?->isAppealPhase
    || $entity->opportunity->parent?->evaluationMethod?->slug !== 'technical') {
    return;
}

$relatorId = (int) ($entity->appealTechnicalCorrectionRelatorUserId ?? 0);
if (!$entity->opportunity->canUser('@control') && $relatorId !== (int) $app->user->id) {
    return;
}

$this->jsObject['config']['appealTechnicalScoreCorrection'] = [
    'enabled' => true,
];
