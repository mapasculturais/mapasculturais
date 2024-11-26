<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;

$this->layout = 'entity';
$this->addOpportunityPhasesToJs();
$this->import('
    entity-field
    mc-confirm-button
    mc-modal
    mc-tab
    mc-tabs
    opportunity-form-view
    opportunity-form-export
    opportunity-form-import
    opportunity-phase-header
    opportunity-filter-configuration
    v1-embed-tool
');
?>
<div class="form-builder__content grid-12">
    <opportunity-phase-header classes="col-12" :phase="entity"></opportunity-phase-header>
    <div class="col-12">
        <h2><?= i::__("Configuração de formulário de coleta de dados") ?></h2>
    </div>
    <opportunity-form-import classes="col-12" :entity="entity"></opportunity-form-import>
    <div class="form-builder__cards col-12 grid-12">
        <div class="col-6 sm:col-12 grid-12" v-if="entity.isFirstPhase">
            <mc-card class="col-12">
                <template #default>
                    <div class="input-group">
                        <h4 class="input-group__title"><?= i::__("Habilitar campo para vínculo de espaço") ?></h4>
                        <h6 class="input-group__subtitle"><?= i::__("Permite que o proponente selecione um espaço para associar à inscrição.") ?></h6>
                        <div class="input-group__inputs no-padding-bottom">
                            <label class="input-group__input"> <input v-model="entity.useSpaceRelationIntituicao" type="radio" name="useSpaceRelationIntituicao" value="dontUse" /> <?= i::_e('Desabilitado') ?> </label>
                            <label class="input-group__input"> <input v-model="entity.useSpaceRelationIntituicao" type="radio" name="useSpaceRelationIntituicao" value="required" /> <?= i::_e('Obrigatório') ?> </label>
                            <label class="input-group__input"> <input v-model="entity.useSpaceRelationIntituicao" type="radio" name="useSpaceRelationIntituicao" value="optional" /> <?= i::_e('Opcional') ?> </label>
                        </div>
                    </div>
                </template>
            </mc-card>
        </div>
        <div class="col-6 sm:col-12 grid-12" v-if="entity.isFirstPhase">

            <mc-card class="col-12">
                <template #default>
                    <div class="input-group">
                        <h4 class="input-group__title"><?= i::__("Habilitar campo de nome de projeto") ?></h4>
                        <h6 class="input-group__subtitle"><?= i::__("Permite que o inscrito dê nome a um projeto no momento da inscrição.") ?></h6>
                        <div class="input-group__inputs no-padding-bottom">
                            <label class="input-group__input"> <input v-model="entity.projectName" :checked="!entity.projectName || entity.projectName == '0'" type="radio" name="projectName" value="0" /> <?= i::_e('Desabilitado') ?> </label>
                            <label class="input-group__input"> <input v-model="entity.projectName" type="radio" name="projectName" value="2" /> <?= i::_e('Obrigatório') ?> </label>
                            <label class="input-group__input"> <input v-model="entity.projectName" type="radio" name="projectName" value="1" /> <?= i::_e('Opcional') ?> </label>
                        </div>
                    </div>
                </template>
            </mc-card>
        </div>

        <div class="col-6 sm:col-12 grid-12" v-if="entity.isFirstPhase">

            <mc-card class="col-12">
                <template #default>
                    <div class="input-group">
                        <h4 class="input-group__title"><?= i::__("Habilitar solicitação de imagem de perfil") ?></h4>
                        <h6 class="input-group__subtitle"><?= i::__("Solicita ao usuário que insira a imagem de perfil no formulário de inscrição") ?></h6>
                        <div class="input-group__inputs">
                            <entity-field class="input-box" :entity="entity" hide-required  :editable="true" prop="requestAgentAvatar" :autosave="3000"></entity-field>
                        </div>
                    </div>
                </template>
            </mc-card>
        </div>

        <div class="col-6 sm:col-12 grid-12" v-if="entity.isFirstPhase">

            <mc-card class="col-12">
                <template #default>
                    <div class="input-group">
                        <h4 class="input-group__title"><?= i::__("Habilitar pergunta 'Vai concorrer às cotas'") ?></h4>
                        <div class="input-group__inputs">
                            <entity-field class="input-box" :entity="entity" hide-required  :editable="true" prop="enableQuotasQuestion" :autosave="3000"></entity-field>
                        </div>
                    </div>
                </template>
            </mc-card>
        </div>
    </div>

    <div class="col-12 grid-12 form-export">
        <div class="col-6"><!-- placeholder --></div>
        <opportunity-form-view :entity="entity" classes="col-3"></opportunity-form-view>
        <opportunity-form-export :entity="entity" classes="col-3"></opportunity-form-export>
    </div>

    <div class="col-12">
        <mc-tabs ref="tabs" v-model:draggable="stepsWithSlugs">
            <template #default>
                <mc-tab v-for="({ step, slug }, index) of stepsWithSlugs" :label="`${index + 1}. ${step.name ?? ''}`" :key="step.id" :slug="slug" :cache="false">
                    <div class="form-builder__step-config">
                        <div>
                            <entity-field :entity="step" prop="name" :autosave="1000" hide-required></entity-field>
    
                            <mc-confirm-button v-if="steps.length > 1" @confirm="deleteStep(step)">
                                <template #button="modal">
                                    <button @click="modal.open()" class="button button--text-danger button--icon">
                                        <?php i::_e('Excluir etapa') ?>
                                        <mc-icon name="trash"></mc-icon>
                                    </button>
                                </template>
    
                                <template #message="message">
                                    <?php i::_e('Deseja remover esta etapa?') ?>
                                </template>
                            </mc-confirm-button>
                        </div>

                        <div>
                            <opportunity-filter-configuration v-model="step.metadata.conditional" @update:modelValue="saveMetadata(step)"></opportunity-filter-configuration>
                        </div>
                    </div>

                    <v1-embed-tool route="formbuilder" :id="entity.id" :params="{ step_id: step.id }" min-height="600px"></v1-embed-tool>
                </mc-tab>
            </template>

            <template #after-tablist>
                <mc-modal title="<?php i::_e('Criar etapa') ?>">
                    <template #button="modal">
                        <button type="button" class="button button--primary button--icon form-builder__add-step" @click="modal.open()">
                            <mc-icon name="add"></mc-icon>
                            <?= i::__('Adicionar etapa') ?>
                        </button>
                    </template>

                    <template #default>
                        <div class="field">
                            <label for="step-name"><?php i::_e('Nome da etapa') ?></label>
                            <input id="step-name" type="text" v-model.trim="newStep.name">
                        </div>
                    </template>

                    <template #actions="modal">
                        <button type="button" class="button button--primary" @click="addStep(modal)"><?php i::_e('Criar') ?></button>
                    </template>
                </mc-modal>
            </template>
        </mc-tabs>
    </div>
</div>
