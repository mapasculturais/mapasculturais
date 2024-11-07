<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>
<div v-if="evaluationDetails" v-for="(detail, index) in evaluationDetails" class="registration-results__card">
    <div class="registration-results__card-header">
        <div class="registration-results__card-title">
            <h4 v-if="detail.valuer" class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> {{detail.valuer.name}}
            </h4>
            <h4 v-else class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> #{{index+1}}
            </h4>
            <p>
                <label>
                    <?= i::__('Resultado final: ') ?>
                </label>
                <strong :class="stausColor(detail.evaluationResult)">
                    <mc-icon name="circle" :class="stausColor(detail.evaluationResult)"></mc-icon>
                    {{statusString(detail.evaluationResult)}}
                </strong>
            </p>
        </div>
    </div>

    <template v-for="(item, index) in detail.evaluations">
        <div v-if="index !== 'valuer'" class="registration-results__card-content">
            <div class="registration-results__opinion registration-results__opinion--document">
                <div class="registration-results__opinion-text">
                    <p>
                        <mc-icon name="circle" :class="stausColor(item.evaluation)"></mc-icon>
                        <strong :class="stausColor(item.evaluation)"> {{statusString(item.evaluation)}} </strong>
                    </p>
                    <p>
                        <strong> {{item.label}} </strong>
                    </p>
                    <br>
                    <h5 class="registration-results__opinion-title bold"><?= i::__('Parecer') ?>:</h5>
                    <div>
                    <ul>
                        <li>
                            {{item.obs}}
                        </li>
                    </ul>

                    </div>
                    <br>
                    <template v-if="item.obs_items">
                    <h5 class="registration-results__opinion-title bold"><?= i::__('Detalhamento') ?>:</h5>
                    <ul>
                        <li>
                            {{item.obs_items}}
                        </li>
                    </ul>
                    </template>
                </div>
            </div>
        </div>
    </template>

</div>