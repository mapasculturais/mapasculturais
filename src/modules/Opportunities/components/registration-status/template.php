<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    registration-results
    mc-loading
');
?>

<div class="opportunity-phases-timeline__box">
    <label class="semibold opportunity-phases-timeline__label"><?= i::__('Resultado da fase:')?></label>
    <div class="opportunity-phases-timeline__status">
        <mc-icon name="circle" :class="verifyState(registration)"></mc-icon>
        <p v-if="registration.status == 10"><?= i::__('Inscrição selecionada') ?></p>
        <p v-if="registration.status == 8"><?= i::__('Inscrição suplente') ?></p>
        <p v-if="registration.status == 3"><?= i::__('Inscrição não selecionada') ?></p>
        <p v-if="registration.status == 2"><?= i::__('Inscrição inválida') ?></p>
        <p v-if="registration.status == 1"><?= i::__('Inscrição enviada') ?></p>
        <p v-if="registration.status == 0"><?= i::__('Inscrição não enviada') ?></p>
    </div>

    <div>
        <div v-if="phase.type == 'qualification'"><?= i::__('Resultado:') ?> <strong>{{registration.consolidatedResult}}</strong></div>
        <div v-if="phase.type == 'technical'"><?= i::__('Pontuação:') ?> <strong>{{formatNote(registration.consolidatedResult)}}</strong></div>
        <div v-if="phase.type == 'documentary'"> 
            <strong v-if="registration.consolidatedResult == '1'">
                <mc-icon name="circle" class="success__color"></mc-icon>
                <?= i::__('Válido') ?>
            </strong>
            <strong v-if="registration.consolidatedResult == '-1'">
                <mc-icon name="circle" class="danger__color"></mc-icon>
                <?= i::__('Inválido') ?>
            </strong>
        </div>
        <registration-results v-if="phase.publishEvaluationDetails" :registration="registration" :phase="phase"></registration-results>
    </div>
</div>

<div v-if="!appealRegistration?.id && registration.status == 3" class="opportunity-phases-timeline__request-appeal">
    <h5 v-if="!processing" class="bold opportunity-phases-timeline__label--lowercase"><?= i::__('Discorda do resultado?')?></h5>
    <button v-if="!processing" class="button button--primary button--primary-outline" @click="createAppealPhaseRegistration()"><?= i::__('Solicitar recurso') ?></button>

    <div v-if="processing" class="col-12">
        <mc-loading :condition="processing"> <?= i::__('carregando') ?></mc-loading>
    </div>
</div>
<div v-if="appealRegistration?.id" class="opportunity-phases-timeline__request-appeal__box">
    <div class="item__dot-appeal-phase"> <span class="dot"></span> </div>
    <div class="item__content">
        <div class="item__content--title"> <?= i::__('[Recurso]') ?> </div>
        <div class="item__content--description">
            <h5 class="semibold"><?= i::__('de') ?> <span v-if="dateFrom()">{{dateFrom()}}</span>
            <?= i::__('a') ?> <span v-if="dateTo()">{{dateTo()}}</span>
            <?= i::__('às') ?> <span v-if="hour()">{{hour()}}</span></h5>
        </div>

        <div class="opportunity-phases-timeline__box">
            <label class="semibold opportunity-phases-timeline__label"><?= i::__('Resultado do recurso:')?></label>
            <div class="opportunity-phases-timeline__status">
                <mc-icon name="circle" :class="verifyState(appealRegistration)"></mc-icon>
                <p v-if="appealRegistration.status == 10"><?= i::__('Deferido') ?></p>
                <p v-if="appealRegistration.status == 3"><?= i::__('Indeferido') ?></p>
                <p v-if="appealRegistration.status == 2"><?= i::__('Recurso inválido') ?></p>
                <p v-if="appealRegistration.status == 1"><?= i::__('Aguardando resposta') ?></p>
                <p v-if="appealRegistration.status == 0"><?= i::__('Recurso não enviado') ?></p>

            </div>
            <registration-results v-if="phase.appealPhase.evaluationMethodConfiguration.publishEvaluationDetails" :registration="appealRegistration" :phase="appealRegistration.opportunity.evaluationMethodConfiguration"></registration-results>
        </div>
        <div v-if="appealRegistration && appealRegistration.status == 0" class="opportunity-phases-timeline__request-appeal">
            <h5 class="bold opportunity-phases-timeline__label--lowercase"><?= i::__('Finalize sua inscrição no recurso:')?></h5>
            <button class="button button--primary button--primary" @click="fillFormButton()"><?= i::__('Preencher formulário') ?></button>
        </div>

    </div>
</div>