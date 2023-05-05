<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->addOpportunityPhasesToJs();

$this->import('
    entity-field
    opportunity-form-builder-category
    opportunity-phase-header
    opportunity-form-import
    opportunity-form-export
    v1-embed-tool
')
?>

<div class="form-builder__content">
    <opportunity-phase-header :phase="entity"></opportunity-phase-header>

    <div class="grid-12 form-builder__label-btn">
        <div class="col-12">
            <h3 class="formtitle"><?= i::__("Configuração de formulário de coleta de dados") ?></h3>
        </div>
    </div>
    <opportunity-form-import :entity="entity"></opportunity-form-import>
    <div class="grid-12">
        <div class="col-6 sm:col-12" v-if="entity.isFirstPhase">
            <opportunity-form-builder-category :entity="entity"></opportunity-form-builder-category>
        </div>

        <div class="col-6 sm:col-12">
            <mapas-card no-title>
                <template #default>
                    <div class="request-data grid-12">
                        <div v-if="entity.isFirstPhase" class="col-12">
                            <h4 class="request-data__title"><?= i::__("Solicitar Agente Coletivo?") ?></h4>
                            <span class="request-data__subtitle"><?= i::__("Permitir inscrição de Agente Coletivo") ?></span>
                            <div class="request-data__inputs">
                                <label class="options"> <input v-model="entity.useAgentRelationColetivo" type="radio" name="useAgentRelationColetivo" value="dontUse" /> <?= i::_e('Não Utilizar') ?> </label>
                                <label class="options"> <input v-model="entity.useAgentRelationColetivo" type="radio" name="useAgentRelationColetivo" value="required" /> <?= i::_e('Obrigatório') ?> </label>
                                <label class="options"> <input v-model="entity.useAgentRelationColetivo" type="radio" name="useAgentRelationColetivo" value="optional" /> <?= i::_e('Opcional') ?> </label>
                            </div>
                        </div>
                        <div v-if="entity.isFirstPhase" class="col-12">
                            <h4 calss="request-data__title"><?= i::__("Solicitar instituição responsável?") ?></h4>
                            <span class="request-data__subtitle"><?= i::__("Solicite a inscrição de instituções (agentes coletivos com CNPJ).") ?></span>
                            <div class="request-data__inputs">
                                <label class="options"> <input v-model="entity.useAgentRelationInstituicao" type="radio" name="useAgentRelationInstituicao" value="dontUse" /> <?= i::_e('Não Utilizar') ?> </label>
                                <label class="options"> <input v-model="entity.useAgentRelationInstituicao" type="radio" name="useAgentRelationInstituicao" value="required" /> <?= i::_e('Obrigatório') ?> </label>
                                <label class="options"> <input v-model="entity.useAgentRelationInstituicao" type="radio" name="useAgentRelationInstituicao" value="optional" /> <?= i::_e('Opcional') ?> </label>
                            </div>
                        </div>
                        <entity-field :entity="entity" prop="registrationLimit" classes="col-12"></entity-field>
                        <entity-field :entity="entity" prop="registrationLimitPerOwner" classes="col-12"></entity-field>
                    </div>
                </template>
            </mapas-card>
        </div>

        <div class="col-6 sm:col-12" v-if="entity.isFirstPhase">
            <mapas-card>
                <template #title>
                    <div class="card-header">
                        <label class="card-header__title"><?= i::__("Permitir vínculo de Espaço?") ?></label>
                        <div class="card-header__subtitle"><?= i::__("Permitir um espaço para associar à inscrição.") ?></div>
                    </div>
                </template>
                <template #default>
                    <div class="grid-12">
                        <entity-field :entity="entity" prop="useSpaceRelationIntituicao" classes="col-12"></entity-field>
                    </div>
                </template>
            </mapas-card>
        </div>

        <div class="col-6 sm:col-12">
            <mapas-card>
                <template #title>
                    <div class="card-header">
                        <label class="card-header__title"><?= i::__("Habilitar informações de Projeto?") ?></label>
                        <div class="card-header__subtitle"><?= i::__("Permitir que proponente vizualise informações básicas sobre um projeto.") ?></div>
                    </div>
                </template>
                <template #default>
                    <div class="grid-12">
                        <entity-field :entity="entity" prop="projectName" classes="col-12"></entity-field>
                    </div>
                </template>
            </mapas-card>
        </div>
        <div class="col-12 form-export">
            <opportunity-form-export :entity="entity"></opportunity-form-export>
        </div>

        <div class="col-12">
            <v1-embed-tool route="formbuilder" :id="entity.id"></v1-embed-tool>
        </div>
    </div>
</div>