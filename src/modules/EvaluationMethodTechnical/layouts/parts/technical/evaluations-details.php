<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

 use MapasCulturais\i;
 
?>
<div class="registration-results__card">
    <div class="registration-results__card-header">
        <div class="registration-results__card-title">
            <h4 class="bold">
                <?= i::__('Resultado Consolidado') ?>
            </h4>
        </div>

        <div>
            <p><label><?= i::__('Pontuação total: ') ?></label> <strong>{{registration.consolidatedResult}}</strong></p>
            <p><label><?= i::__('Pontuação máxima: ') ?></label> <strong>{{registration.consolidatedDetails.maxScore}}</strong></p>
        </div>
    </div>

    <div v-if="registration.consolidatedDetails.appliedPointReward" 
         v-for="policy in [registration.consolidatedDetails.appliedPointReward]" class="registration-results__card-content">                    
        <div class="registration-results__opinion registration-results__opinion--document">
            <h5 class="registration-results__opinion-title bold">
                <?= i::__('Bônus por pontuação') ?>
            </h5>

            <div>
                <p><label><?= i::__('Pontuação original: ') ?></label> <strong>{{policy.raw}}</strong></p>
                <p><label><?= i::__('Acréscimos:') ?></label>
                    <ul>
                        <li v-for="rule in policy.rules">{{rule.field.title}}: <em>{{rule.value}}</em> <strong>(+{{rule.percentage}}%)</strong></li>
                    </ul>
                </p>
                <p>
                    <label><?= i::__('Acréscimo total na pontuação:') ?> </label> 
                    <strong>{{parseFloat(policy.raw) / 100 * parseFloat(policy.percentage)}}
                        <em>({{policy.percentage}}%)</em></strong>
                </p>
                <p v-if="parseFloat(policy.percentage) >= parseFloat(policy.roof)">
                    <label><?= i::__('Percentual máximo que pode ser acrescido:') ?></label>
                    <strong>{{policy.roof}}%</strong>
                </p>
            </div>
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

            <p><label><?= i::__('Pontuação total: ') ?></label> <strong>{{registration.consolidatedResult}}</strong></p>    
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
            <h5 class="registration-results__opinion-title bold"><?= i::__('Detalhamento da pontuação') ?>:</h5>
            <ul>
                <li v-for="section in evaluation.scores">
                    {{section.name}}: <strong>{{section.score}}</strong> <em>(<?= i::__('pontuação máxima') ?>: {{section.maxScore}})</em>
                    <ul>
                        <li v-for="cri in section.criteria">
                            {{cri.title}}: <strong>{{cri.score}} </strong> <em>(<?= i::__('pontuação máxima') ?>: {{cri.maxScore}})</em>
                        </li>
                    </ul>
                </li>
            </ul>

        </div>
    </div>
</div>