<?php

/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-stepper-vertical
    opportunity-create-evaluation-phase
    opportunity-create-data-collect-phase
    mc-link
');
?>
<mc-stepper-vertical :items="phases" allow-multiple>
    <template #header-title="{index, item}">
        <h2 v-if="index">{{item.name}}</h2>
        <h2 v-if="!index"><?= i::__('Período de inscrição') ?></h2>
        <p v-if="item.__objectType == 'opportunity'">
            <?= i::__('Tipo') ?>: <?= i::__('Coleta de dados') ?>
        </p>
        <p v-if="item.__objectType == 'evaluationmethodconfiguration'">
            <?= i::__('Tipo') ?>: {{item.type.name}}
        </p>
    </template>
    <template #default="{index, item}">
        <div v-if="index > 0">
            <entity-field :entity="item" prop="name" hide-required></entity-field>
        </div>

        <template v-if="item.__objectType == 'opportunity'">
            <mapas-card>
                <div class="grid-12">
                    <div class="col-12">
                        <h3><?= i::__("Configuração da fase") ?></h3>
                    </div>
                    <entity-field :entity="item" prop="registrationFrom" classes="col-6 sm:col-12" :min="getMinDate(item.__objectType, index)" :max="getMaxDate(item.__objectType, index)"></entity-field>
                    <entity-field :entity="item" prop="registrationTo" classes="col-6 sm:col-12"></entity-field>
                    <div class="col-12">
                        <h5>
                            <mc-icon name="info"></mc-icon> <?= i::__("A configuração desse formulário está pendente") ?>
                        </h5>
                    </div>
                    <div class="col-12">
                        <button class="button--primary button"><?= i::__("Configurar formulário") ?></button>
                    </div>
                    <div class="col-12">
                        <a href="#"><mc-icon name="trash"></mc-icon><?= i::__("Excluir etapa de fase") ?></a>
                    </div>
                </div>
            </mapas-card>
        </template>

        <template v-if="item.__objectType == 'evaluationmethodconfiguration'">
            <entity-field :entity="item" prop="evaluationFrom" hide-required></entity-field>
            <entity-field :entity="item" prop="evaluationTo" hide-required></entity-field>
        </template>
    </template>
    <template #after-li="{index, item}">
        <div v-if="index==1" class="grid-12">
            <div class="col-12">
                <opportunity-create-evaluation-phase :opportunity="entity" :previousPhase="item" :lastPhase="phases[index+1]"></opportunity-create-evaluation-phase>
            </div>
            <div class="col-12">
                <opportunity-create-data-collect-phase :opportunity="entity" :previousPhase="item" :lastPhase="phases[index+1]"></opportunity-create-data-collect-phase>
            </div>
        </div>
    </template>
</mc-stepper-vertical>