<?php
use MapasCulturais\i;

$phases = $entity->firstPhase->phases;
$phasesToJs = [];

foreach($phases as $phase) {
    if ($phase->{'@entityType'} == 'opportunity') {
        $opportunity = $app->repo('Opportunity')->find($phase->id);
        if ($opportunity->isSupportUser($app->user)) {
            $phasesToJs[] = $opportunity;
        }
    }
}

if (count($phasesToJs) == 0) {
    return;
}

$this->jsObject['supportPhases'] = $phasesToJs;

$this->import('
    opportunity-phase-support
');
?>

<tab label="<?= i::__('Suporte') ?>" slug="support">
    <div class="opportunity-container">
        <opportunity-phase-support></opportunity-phase-support>
    </div>
</tab>
