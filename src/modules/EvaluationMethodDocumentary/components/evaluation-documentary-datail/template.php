<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>
<div v-if="registration.evaluationsDetails.length" v-for="(detail, index) in registration.evaluationsDetails" class="registration-results__card">
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
                <strong class="has-color" :class="statusColor(detail.evaluationResult)">
                    <mc-icon name="circle" class="has-color"></mc-icon>
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
                        <mc-icon name="circle" class="has-color" :class="statusColor(item.evaluation)"></mc-icon>
                        <strong class="has-color"> {{statusString(item.evaluation)}} </strong>
                    </p>
                    <p>
                        <strong> {{item.label}} </strong>
                    </p>
                    <br>
                    <h5 class="registration-results__opinion-title bold"><?= i::__('Parecer') ?>:</h5>
                    <p>
                    <ul>
                        <li>
                            {{item.obs}}
                        </li>
                    </ul>

                    </p>
                    <br>
                    <p v-if="item.obs_items">
                    <h5 class="registration-results__opinion-title bold"><?= i::__('Detalhamento') ?>:</h5>
                    <ul>
                        <li>
                            {{item.obs_items}}
                        </li>
                    </ul>
                    </p>
                </div>
            </div>
        </div>
    </template>

</div>