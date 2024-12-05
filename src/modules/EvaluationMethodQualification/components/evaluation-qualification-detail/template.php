<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
')
?>
<div class="registration-results__card">
    <div class="registration-results__card-header">
        <div class="registration-results__card-title">
            <h4 class="bold">
                <?= i::__('Resultado Consolidado') ?>
            </h4>
        </div>

        <div>
            <p>
                <label>
                    <?= i::__('Resultado: ') ?>
                </label>
                <strong v-if="registration.consolidatedResult === 'Habilitado'" class="success__color">
                    <mc-icon name="circle" class="success__color"></mc-icon>{{registration.score}}
                    {{registration.consolidatedResult}}
                </strong>
                <strong v-if="registration.consolidatedResult === 'Inabilitado'" class="danger__color">
                    <mc-icon name="circle" class="danger__color"></mc-icon>{{registration.consolidatedResult}}
                    {{registration.consolidatedResult}}
                </strong>
            </p>
        </div>
    </div>
</div>

<div v-if="evaluationDetails" v-for="(evaluation, index) in evaluationDetails" class="registration-results__card">
    <div class="registration-results__card-header">
        <div class="registration-results__card-title">
            <h4 v-if="evaluation.valuer" class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> {{evaluation.valuer.name}}
            </h4>

            <p>
                <label><?= i::__('Resultado: ') ?></label>
                <strong v-if="evaluation.result === 'valid'" class="success__color">
                    <mc-icon name="circle" class="success__color"></mc-icon><?= i::__('Habilitado') ?>
                </strong>
                <strong v-if="evaluation.result === 'invalid'" class="danger__color">
                    <mc-icon name="circle" class="danger__color"></mc-icon><?= i::__('Inabilitado') ?>
                </strong>
            </p>

        </div>
    </div>
    <div class="registration-results__card-content">
        <div class="registration-results__opinion registration-results__opinion--document">
            <h5 class="registration-results__opinion-title bold"><?= i::__('Parecer') ?>:</h5>
            <div class="registration-results__opinion-text">
                <p>{{evaluation.obs}}</p>
            </div>
        </div>
    </div>
    <div class="registration-results__card-content">
        <div class="registration-results__opinion registration-results__opinion--document">
            <h5 class="registration-results__opinion-title bold"><?= i::__('Detalhamento da avaliação') ?>:</h5>
            <ul>
                <template v-for="section in evaluation.scores">
                    <li v-if="showSectionAndCriterion(section)">
                        {{section.name}}:
                        <ul>
                            <li v-for="cri in section.criteria">
                                {{cri.name}}:
                                <strong v-if="cri.result[0] == 'valid'" class="success__color">{{ formatResult(cri.result) }}</strong>
                                <strong v-if="cri.result[0] == 'invalid'" class="danger__color" style="white-space: pre-line;">{{ formatResult(cri.result) }}</strong>
                            </li>
                        </ul>
                    </li>
                </template>

            </ul>

        </div>
    </div>
</div>