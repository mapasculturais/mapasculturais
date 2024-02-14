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
                <label><?= i::__('Resultado: ') ?></label>
                <strong v-if="registration.consolidatedResult === 'Habilitado'" class="success__color">
                    <mc-icon name="circle" class="success__color"></mc-icon>{{registration.consolidatedResult}}
                </strong>
                <strong v-if="registration.consolidatedResult === 'Inabilitado'" class="danger__color">
                    <mc-icon name="circle" class="danger__color"></mc-icon>{{registration.consolidatedResult}}
                </strong>
            </p>
        </div>
    </div>
</div>

<div v-if="registration.evaluationsDetails.length" v-for="(evaluation, index) in registration.evaluationsDetails" class="registration-results__card">
    <div class="registration-results__card-header">
        <div class="registration-results__card-title">
            <h4 v-if="evaluation.valuer" class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> {{evaluation.valuer.name}}
            </h4>
            <h4 v-else class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> #{{index+1}}
            </h4>

            <p>
                <label><?= i::__('Resultado: ') ?></label>

                <strong v-if="registration.consolidatedResult === 'Habilitado'" class="success__color">
                    <mc-icon name="circle" class="success__color"></mc-icon>{{registration.consolidatedResult}}
                </strong>
                <strong v-if="registration.consolidatedResult === 'Inabilitado'" class="danger__color">
                    <mc-icon name="circle" class="danger__color"></mc-icon>{{registration.consolidatedResult}}
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
                <li v-for="section in evaluation.scores">
                    {{section.name}}:
                    <ul>
                        <li v-for="cri in section.criteria">
                            {{cri.name}}: {{cri.result}}
                        </li>
                    </ul>
                </li>
            </ul>

        </div>
    </div>
</div>