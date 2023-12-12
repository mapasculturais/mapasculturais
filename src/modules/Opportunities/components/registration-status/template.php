<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    registration-results
');
?>

<div class="opportunity-phases-timeline__box">
    <label class="semibold opportunity-phases-timeline__label"><?= i::__('RESULTADO DA FASE')?></label>
    <div class="opportunity-phases-timeline__status">
        <mc-icon name="circle" :class="verifyState(registration)"></mc-icon>
        <p v-if="registration.status == 10"><?= i::__('Inscrição selecionada') ?></p>
        <p v-if="registration.status == 8"><?= i::__('Inscrição suplente') ?></p>
        <p v-if="registration.status == 3"><?= i::__('Inscrição não selecionada') ?></p>
        <p v-if="registration.status == 2"><?= i::__('Inscrição inválida') ?></p>
        <p v-if="registration.status == 0"><?= i::__('Inscrição não enviada') ?></p>
    </div>

    <template v-if="phase.type == 'technical' || phase.type == 'documentary'">
        <div><?= i::__('Pontuação:') ?> <strong>{{formatNote(registration.consolidatedResult)}}</strong></div>
    
        <registration-results v-if="phase.publishEvaluationDetails" :registration="registration" :phase="phase"></registration-results>
    </template>
</div>