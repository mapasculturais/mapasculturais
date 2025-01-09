<?php /**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div v-if="evaluationDetails" v-for="(evaluation, index) in evaluationDetails" class="registration-results__card">
    <div class="registration-results__card-header">
        <div class="registration-results__card-title">
            <h4 v-if="evaluation.valuer" class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> {{evaluation.valuer.name}}
            </h4>
            <h4 v-else class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> #{{index+1}}
            </h4>
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
</div>