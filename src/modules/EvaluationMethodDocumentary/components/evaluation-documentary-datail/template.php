<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>
<div v-if="registration.evaluationsDetails.length" v-for="(evaluation, index) in registration.evaluationsDetails" class="registration-results__card">
    <div class="registration-results__card-header">
        <div class="registration-results__card-title">
            <h4 class="registration-results__opinion-title bold">
                <?= i::__('Avaliação: ') ?> {{index+1}}
            </h4>
            <h4 v-if="evaluation.valuer" class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> {{evaluation.valuer.name}}
            </h4>
        </div>
    </div>
    <template v-for="(item, index) in evaluation">
        <div v-if="index !== 'valuer'" class="registration-results__card-content">
            <div class="registration-results__opinion registration-results__opinion--document">
                <div class="registration-results__opinion-text">
                    <div>
                        <strong>{{item.label}}</strong>
                    </div>
                    <div v-if="item.evaluation == 'valid'">
                        <mc-icon name="circle" class="success__color"></mc-icon>
                        <?= i::__('Válido: ') ?>
                    </div>
                    <div v-if="item.evaluation == 'invalid' || item.evaluation == '' ">
                        <mc-icon name="circle" class="danger__color"></mc-icon>
                        <?= i::__('Inválido: ') ?>
                    </div>
                    <div>
                        {{item.obs}}
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>