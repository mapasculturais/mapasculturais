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
        <div class="phase-stepper">
            <h2 class="phase-stepper__name" v-if="index">{{item.name}}</h2>
            <h2  class="phase-stepper__period" v-if="!index"><?= i::__('Período de inscrição') ?></h2>
            <p class="phase-stepper__type"v-if="item.__objectType == 'opportunity'">
                <label class="phase-stepper__type--name"><?= i::__('Tipo') ?></label class="phase-stepper__type--label">: <label class="phase-stepper__type--item"><?= i::__('Coleta de dados') ?></label>
            </p>
            <p class="" v-if="item.__objectType == 'evaluationmethodconfiguration'">
                <?= i::__('Tipo') ?>: {{item.type.name}}
            </p>
        </div>
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
                    <div class="add-phase col-12">
                        <button class="button--primary button"><?= i::__("Configurar formulário") ?></button>
                    </div>
                    <div class="add-phase col-12">
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
        <div v-if="index==1" class="add-phase grid-12">
            <div class="add-phase__evaluation col-12">
                <opportunity-create-evaluation-phase :opportunity="entity" :previousPhase="item" :lastPhase="phases[index+1]"></opportunity-create-evaluation-phase>
            </div>
            <p><label class="add-phase"><?= i::__("ou")?></label></p>
            <div class="add-phase__collection col-12">
                <opportunity-create-data-collect-phase :opportunity="entity" :previousPhase="item" :lastPhase="phases[index+1]"></opportunity-create-data-collect-phase>
            </div>
        </div>
    </template>
</mc-stepper-vertical>