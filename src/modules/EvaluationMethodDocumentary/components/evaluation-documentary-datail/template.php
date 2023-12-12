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
            <p :class="stausColor(detail.evaluationResult)">
                <mc-icon name="circle" :class="stausColor(detail.evaluationResult)"></mc-icon>
                <strong> {{statusString(detail.evaluationResult)}} </strong>
            </p>
        </div>
    </div>

    <template v-for="(item, index) in detail.evaluations">
        <div v-if="index !== 'valuer'" class="registration-results__card-content">
            <div class="registration-results__opinion registration-results__opinion--document">
                <div class="registration-results__opinion-text">
                    <p>
                        <strong>{{item.label}}</strong>
                    </p>
                    <p :class="stausColor(item.evaluation)">
                        <mc-icon name="circle" :class="stausColor(item.evaluation)"></mc-icon>
                        <strong>{{statusString(item.evaluation)}}</strong>
                    </p>
                    <p>
                        {{item.obs}}
                    </p>
                </div>
            </div>
        </div>
    </template>

</div>