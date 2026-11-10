<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-status
    registration-results
    mc-loading
');
?>
<div v-if="!phase.isLastPhase" class="opportunity-phases-timeline__box">
    <label class="semibold opportunity-phases-timeline__label"><?= i::__('Resultado preliminar:')?></label>
    <!-- R02 (#66): com valor formatado, o resultado substitui o status da fase -->
    <mc-status v-if="!hasMethodResult(preliminarySnapshotValue)" :status-name="getStatusDisplay(registration)"></mc-status>

    <!--
        R02 (#66): snapshot do resultado preliminar (#64) quando há resultado
        publicado. FORA do gate showResults (detalhamento por método): o
        snapshot é RESULTADO, não detalhe — sem isso, métodos sem
        detalhamento (ex.: simples, sem publishEvaluationDetails) nunca o
        exibiam e a box ficava só no mc-status vigente.
    -->
    <template v-if="preliminarySnapshotValue !== null">
        <div v-if="phaseType == 'qualification'"><?= i::__('Resultado:') ?> <strong :class="qualificationColor(preliminarySnapshotValue)"><mc-icon name="circle" :class="qualificationColor(preliminarySnapshotValue)"></mc-icon> {{ qualificationLabel(preliminarySnapshotValue) }}</strong></div>
        <div v-if="phaseType == 'technical'"><?= i::__('Pontuação:') ?> <strong>{{ formatNote(preliminarySnapshotValue) }}</strong></div>
        <div v-if="phaseType == 'documentary'">
            <strong v-if="preliminarySnapshotValue == '1'">
                <mc-icon name="circle" class="success__color"></mc-icon>
                <?= i::__('Válido') ?>
            </strong>
            <strong v-if="preliminarySnapshotValue == '-1'">
                <mc-icon name="circle" class="danger__color"></mc-icon>
                <?= i::__('Inválido') ?>
            </strong>
        </div>
        <div v-if="phaseType == 'simple'"><?= i::__('Status:') ?> <strong :class="simpleStatusColor(preliminarySnapshotValue)"><mc-icon name="circle" :class="simpleStatusColor(preliminarySnapshotValue)"></mc-icon> {{ simpleStatusLabel(preliminarySnapshotValue) }}</strong></div>
    </template>

    <div v-if="showResults(phase)">
        <!--
            Sem publicação (ou sem snapshot — ex.: final direto sem
            preliminar), mantém o comportamento vigente (consolidado atual).
        -->
        <template v-if="preliminarySnapshotValue === null">
            <div v-if="phaseType == 'qualification'"><?= i::__('Resultado:') ?> <strong>{{registration.consolidatedResult}}</strong></div>
            <div v-if="phaseType == 'technical'"><?= i::__('Pontuação:') ?> <strong>{{formatNote(registration.consolidatedResult)}}</strong></div>
            <div v-if="phaseType == 'documentary'">
                <strong v-if="registration.consolidatedResult == '1'">
                    <mc-icon name="circle" class="success__color"></mc-icon>
                    <?= i::__('Válido') ?>
                </strong>
                <strong v-if="registration.consolidatedResult == '-1'">
                    <mc-icon name="circle" class="danger__color"></mc-icon>
                    <?= i::__('Inválido') ?>
                </strong>
            </div>
        </template>

        <div class="opportunity-phases-timeline__buttons">
            <div class="registration-results" v-if="registration.opportunity.isReportingPhase === '1' || registration.opportunity.isFinalReportingPhase === '1'">
                <button class="button button--primary button--sm button--large" @click="redirectToRegistrationForm()"><?php i::_e('Visualizar relatório') ?></button>
            </div>
            <registration-results v-if="showRegistrationResults" :registration="registration" :phase="phase"></registration-results>
        </div>
    </div>
</div>

<div v-if="canShowAppeal && appealPhase && !appealRegistration" class="opportunity-phases-timeline__request-appeal">
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
        <div v-if="showPhaseDates()" class="item__content--description">
            <h5 class="semibold"><?= i::__('de') ?> <span v-if="dateFrom()">{{dateFrom()}}</span>
            <?= i::__('a') ?> <span v-if="dateTo()">{{dateTo()}}</span>
            <?= i::__('às') ?> <span v-if="hour()">{{hour()}}</span></h5>
        </div>

        <div v-if="appealRegistration.status > 0" class="opportunity-phases-timeline__box">
            <div>
                <label class="semibold opportunity-phases-timeline__label"><?= i::__('Resultado do recurso:')?></label>
                <mc-status :status-name="getStatusDisplay(appealRegistration)"></mc-status>
            </div>
            <registration-results :registration="appealRegistration" :phase="appealRegistration.opportunity.evaluationMethodConfiguration"></registration-results>
        </div>
        <div v-if="appealRegistration && appealRegistration.status == 0" class="opportunity-phases-timeline__request-appeal">
            <h5 class="bold opportunity-phases-timeline__label--lowercase"><?= i::__('Finalize sua inscrição no recurso:')?></h5>
            <button class="button button--primary button--primary" @click="fillFormButton()"><?= i::__('Preencher formulário') ?></button>
        </div>

    </div>
</div>

<!--
    R02 (#66): box RESULTADO FINAL — mesma estrutura do box de resultado
    (markup/classes do box de fase); só o resultado final consolidado por
    método, sem notas preliminares nem passos. Não renderiza sem publicação
    final (publishedRegistrations — coluna ORM; risco D2 resolvido com fetch
    das flags quando o payload da fase não as carrega).
-->
<div v-if="!phase.isLastPhase && publishState.final" class="opportunity-phases-timeline__box">
    <label class="semibold opportunity-phases-timeline__label"><?= i::__('Resultado final:')?></label>
    <!-- R02 (#66): com valor formatado, o resultado substitui o status da fase -->
    <mc-status v-if="!hasMethodResult(registration.consolidatedResult)" :status-name="getStatusDisplay(registration)"></mc-status>

    <!--
        R02 (#66): valor final consolidado por método FORA do gate
        showResults (mesma doutrina do snapshot: resultado é resultado, não
        detalhe — simples sem publishEvaluationDetails não passava no gate e
        a box ficava só no mc-status). Fonte: o vigente já exposto ao dono.
    -->
    <div>
        <div v-if="phaseType == 'qualification'"><?= i::__('Resultado:') ?> <strong :class="qualificationColor(registration.consolidatedResult)"><mc-icon name="circle" :class="qualificationColor(registration.consolidatedResult)"></mc-icon> {{ qualificationLabel(registration.consolidatedResult) }}</strong></div>
        <div v-if="phaseType == 'technical'"><?= i::__('Pontuação:') ?> <strong>{{formatNote(registration.consolidatedResult)}}</strong></div>
        <div v-if="phaseType == 'documentary'">
            <strong v-if="registration.consolidatedResult == '1'">
                <mc-icon name="circle" class="success__color"></mc-icon>
                <?= i::__('Válido') ?>
            </strong>
            <strong v-if="registration.consolidatedResult == '-1'">
                <mc-icon name="circle" class="danger__color"></mc-icon>
                <?= i::__('Inválido') ?>
            </strong>
        </div>
        <!--
            R03 (#78): no método simples, o resultado FINAL aplicado é o
            status da inscrição (recurso deferido pode torná-lo Suplente etc.,
            enquanto o consolidatedResult do MIN avaliações ainda reflete o
            estágio anterior — evidência 65/67: consolidated=3/status=8).
            Os demais métodos seguem com consolidatedResult (pendência
            técnica declarada na issue — não mudar sem evidência).
        -->
        <div v-if="phaseType == 'simple'"><?= i::__('Status:') ?> <strong :class="simpleStatusColor(registration.status)"><mc-icon name="circle" :class="simpleStatusColor(registration.status)"></mc-icon> {{ simpleStatusLabel(registration.status) }}</strong></div>
    </div>
</div>
