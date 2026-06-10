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
            <p><label><?= i::__('Pontuação total: ') ?></label> <strong>{{registration.score}}</strong></p>
            <p><label><?= i::__('Pontuação máxima: ') ?></label> <strong>{{registration.consolidatedDetails.maxScore}}</strong></p>
        </div>
    </div>

    <div v-if="registration.consolidatedDetails.appliedPointReward"
         v-for="policy in [registration.consolidatedDetails.appliedPointReward]" class="registration-results__card-content">
        <div class="registration-results__opinion registration-results__opinion__document">
            <h5 class="registration-results__opinion-title bold">
                <?= i::__('Bônus por pontuação') ?>
            </h5>

            <div>
                <p><label><?= i::__('Pontuação original: ') ?></label> <strong>{{policy.raw}}</strong></p>
                <p><label><?= i::__('Acréscimos:') ?></label></p>
                <ul>
                    <template v-if="policy.type === 'fixed'">
                        <li v-for="rule in policy.rules">
                            {{rule.field.title}}: <em>{{rule.value}}</em>
                            <strong>(+{{rule.bonusValue ?? rule.percentage}} <?= i::__('ponto(s)') ?>)</strong>
                        </li>
                    </template>
                    <template v-else>
                        <li v-for="rule in policy.rules">
                            {{rule.field.title}}: <em>{{rule.value}}</em>
                            <strong>(+{{rule.bonusValue ?? rule.percentage}}%)</strong>
                        </li>
                    </template>
                </ul>

                <template v-if="policy.type === 'fixed'">
                    <p>
                        <label><?= i::__('Acréscimo total na pontuação:') ?> </label>
                        <strong>+{{policy.fixed ?? 0}} <?= i::__('ponto(s)') ?></strong>
                    </p>
                    <p v-if="parseFloat(policy.roof) > 0 && parseFloat(policy.fixed) >= parseFloat(policy.roof)">
                        <label><?= i::__('Pontuação máxima de bônus:') ?></label>
                        <strong>{{policy.roof}} <?= i::__('ponto(s)') ?></strong>
                    </p>
                </template>
                <template v-else>
                    <p>
                        <label><?= i::__('Acréscimo total na pontuação:') ?> </label>
                        <strong>{{parseFloat(policy.raw) / 100 * parseFloat(policy.percentage)}}
                            <em>({{policy.percentage}}%)</em></strong>
                    </p>
                    <p v-if="parseFloat(policy.roof) > 0 && parseFloat(policy.percentage) >= parseFloat(policy.roof)">
                        <label><?= i::__('Percentual máximo que pode ser acrescido:') ?></label>
                        <strong>{{policy.roof}}%</strong>
                    </p>
                </template>
            </div>
        </div>
    </div>
</div>

<div v-if="registration.evaluationsDetails.length" v-for="(evaluation, index) in registration.evaluationsDetails" class="registration-results__card">
    <div class="registration-results__card-header">
        <div class="registration-results__card-title">
            <h4 v-if="evaluation.valuer && evaluation.committeeSequentialNumber" class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> #{{evaluation.committeeSequentialNumber}} - ({{evaluation.valuer.id}} - {{evaluation.valuer.name}})
            </h4>
            <h4 v-else-if="evaluation.valuer" class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> #{{index+1}} - ({{evaluation.valuer.id}} - {{evaluation.valuer.name}})
            </h4>
            <h4 v-else-if="evaluation.committeeSequentialNumber" class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> #{{evaluation.committeeSequentialNumber}}
            </h4>
            <h4 v-else class="registration-results__opinion-title bold">
                <?= i::__('Parecerista: ') ?> #{{index+1}}
            </h4>

            <p><label><?= i::__('Pontuação total: ') ?></label> <strong>{{registration.consolidatedResult}}</strong></p>    
        </div>
    </div>
    <div class="registration-results__card-content">
        <div class="registration-results__opinion registration-results__opinion__document">
        <h5 class="registration-results__opinion-title bold"><?= i::__('Parecer') ?>:</h5>
            <div class="registration-results__opinion-text">
                <p>{{evaluation.obs}}</p>
            </div>
        </div>
    </div>
    <div class="registration-results__card-content">
        <div class="registration-results__opinion registration-results__opinion__document">
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