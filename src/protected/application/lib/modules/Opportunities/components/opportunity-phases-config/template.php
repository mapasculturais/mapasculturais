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
    mc-link
    opportunity-create-data-collect-phase
');
?>
<mc-stepper-vertical :items="phases" allow-multiple>
    <template #header-title="{index, item}">
        <div class="phase-stepper">
            <h2 v-if="index" class="phase-stepper__name">{{item.name}}</h2>
            <h2 v-if="!index" class="phase-stepper__period"><?= i::__('Período de inscrição') ?></h2>
            <p class="phase-stepper__type" v-if="item.__objectType == 'opportunity'">
                <label class="phase-stepper__type--name"><?= i::__('Tipo') ?></label>:
                <label class="phase-stepper__type--item"><?= i::__('Coleta de dados') ?></label>
            </p>
            <p v-if="item.__objectType == 'evaluationmethodconfiguration'" class="phase-stepper__type">
                <label class="phase-stepper__type--name"><?= i::__('Tipo') ?></label>: <label class="phase-stepper__type--item">{{item.type.name}}</label>
            </p>
        </div>
    </template>
    <template #default="{index, item}">
        <div v-if="index > 0" class="config-input">
            <entity-field :entity="item" prop="name" label="Título" hide-required></entity-field>
        </div>
        <template v-if="item.__objectType == 'opportunity'">
            <mapas-card>
                <div class="config-phase grid-12">
                    <div class="config-phase__line-up col-12 "></div>
                    <div class="config-phase__title col-12">
                        <h3 class="config-phase__title--title"><?= i::__("Configuração da fase") ?></h3>
                    </div>
                    <entity-field :entity="item" prop="registrationFrom" classes="col-6 sm:col-12" :min="getMinDate(item.__objectType, index)" :max="getMaxDate(item.__objectType, index)"></entity-field>
                    <entity-field :entity="item" prop="registrationTo" classes="col-6 sm:col-12" :min="getMinDate(item.__objectType, index)" :max="getMaxDate(item.__objectType, index)"></entity-field>
                    <div class="config-phase__info col-12">
                        <h5 class="config-phase__info--message">
                            <mc-icon name="info"></mc-icon> <?= i::__("A configuração desse formulário está pendente") ?>
                        </h5>
                    </div>
                    <div class="col-12">
                        <button class="config-phase__info--button button--primary button"><label class="config-phase__info-button--label"><?= i::__("Configurar formulário") ?></label><mc-icon class="config-phase__info-button--icon" name="external"></mc-icon></button>
                    </div>

                    <div class="config-phase__line-bottom col-12 "></div>
                    <div class="phase-delete col-6" v-if="!item.isLastPhase && !item.isFirstPhase">
                        <confirm-button message="Confirma a execução da ação?" @confirm="deletePhase($event, item, index)">
                            <template #button="modal">
                                <a class="phase-delete__trash" @click="modal.open()">
                                    <mc-icon name="trash"></mc-icon>
                                    <label class="phase-delete__label">{{ text('excluir_fase_coleta_dados') }}</label>
                                </a>
                            </template>
                        </confirm-button>
                    </div>
                    <div class="phase-delete col-6">
                        <a @click="item.save()" class="phase-delete__trash " href="#"><mc-icon name="upload"></mc-icon><label class="phase-delete__label"><?= i::__("Salvar") ?></label></a>
                    </div>
                </div>
            </mapas-card>
        </template>

        <template v-if="item.__objectType == 'evaluationmethodconfiguration'">

            <mapas-card>
                <div class="evaluation-step grid-12">

                    <entity-field :entity="item" prop="evaluationFrom" classes="col-6 sm:col-12" :min="getMinDate(item.__objectType, index)" :max="getMaxDate(item.__objectType, index)"></entity-field>
                    <entity-field :entity="item" prop="evaluationTo" classes="col-6 sm:col-12" :min="getMinDate(item.__objectType, index)" :max="getMaxDate(item.__objectType, index)"></entity-field>
                    <div class="evaluation-box col-12">
                        <div class="evaluation-box__line">

                        </div>
                        <h2 class="evaluation-box__title"><?= i::__("Configuração da avaliação") ?></h2>
                        <span class="evaluation-box__content"><?= i::__("A avaliação simplificada consiste num select box com os status possíveis para uma inscrição.") ?></span>
                    </div>
                    <div class="evaluation-simple col-12">
                        <h3 class="evaluation-simple__title"><?= i::__("Comissão de avaliação simplificada") ?></h3>
                        <span class="evaluation-simple__text"><?= i::__("Defina os agentes que serão avaliadores desta fase.") ?></span>
                    </div>
                    <div class="evaluation-open col-12">
                        <button class="evaluation-open__button button--primary button"><mc-icon name="add"></mc-icon><label><?= i::__("Adicionar pessoa avaliadora") ?></label></button>
                    </div>
                    <div class="evaluation-view col-12">
                        <h2 class="evaluation-view__title"><?= i::__("Configurar campos visíveis para os avaliadores") ?></h2>
                        <span class="evaluation-view__text"><?= i::__("Defina quais campos serão habilitados para avaliação.") ?></span>
                    </div>
                    <div class="evaluation-step__btn col-12">
                        <button class="evaluation-step__btn--secondary  button--secondarylight button"><?= i::__("Abrir lista de campos") ?></button>
                    </div>
                    <div class="evaluation-text col-12">
                        <h3><?= i::__("Adicionar textos explicativos das avaliações") ?></h3>
                    </div>
                    <div class="evaluation-config col-12 field">
                        <label> <?= i::__("Texto configuração geral") ?>
                        </label>
                        <textarea v-model="infos['general']" class="evaluation-config__area" rows="10"></textarea>
                    </div>
                    <div class="col-6 sm:col-12 field" v-for="category in categories">
                        <label> {{ category }}
                            <textarea v-model="infos[category]" style="width: 100%" rows="10"></textarea>
                        </label>
                    </div>
                    <div class="config-phase__line-bottom col-12"></div>
                    <div class="phase-delete col-6" v-if="!item.isLastPhase && !item.isFirstPhase">
                        <confirm-button message="Confirma a execução da ação?" @confirm="deletePhase($event, item, index)">
                            <template #button="modal">
                                <a class="phase-delete__trash" @click="modal.open()">
                                    <mc-icon name="trash"></mc-icon>
                                    <label class="phase-delete__label">{{ text('excluir_fase_avaliacao') }}</label>
                                </a>
                            </template>
                        </confirm-button>
                    </div>
                    <div class="phase-delete col-6">
                        <a @click="item.save()" class="phase-delete__trash " href="#"><mc-icon name="upload"></mc-icon><label class="phase-delete__label"><?= i::__("Salvar") ?></label></a>
                    </div>
                </div>
            </mapas-card>
        </template>
    </template>
    <template #after-li="{index, item}">
        <div v-if="index == phases.length-2" class="add-phase grid-12">
            <div class="add-phase__evaluation col-12">
                <opportunity-create-evaluation-phase :opportunity="entity" :previousPhase="item" :lastPhase="phases[index+1]" @create="addInPhases"></opportunity-create-evaluation-phase>
            </div>
            <p><label class="add-phase__collection"><?= i::__("ou") ?></label></p>
            <div class="add-phase__collection col-12">
                <opportunity-create-data-collect-phase :opportunity="entity" :previousPhase="item" :lastPhase="phases[index+1]" @create="addInPhases"></opportunity-create-data-collect-phase>
            </div>
        </div>
    </template>
</mc-stepper-vertical>