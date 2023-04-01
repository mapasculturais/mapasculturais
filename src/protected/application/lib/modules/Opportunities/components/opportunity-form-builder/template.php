<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->addOpportunityPhasesToJs();

$this->import('
    entity-field
    opportunity-form-builder-category
    opportunity-phase-header
    v1-embed-tool
')
?>

<div class="form-builder__content">
    <opportunity-phase-header :phase="entity"></opportunity-phase-header>

    <div class="grid-12 form-builder__label-btn">
        <div class="col-12">
            <h3><?= i::__("Configuração de formulário de coleta de dados") ?></h3>
        </div>
    </div>

    <div class="grid-12">
        <div class="col-6 sm:col-12" v-if="entity.isFirstPhase">
            <opportunity-form-builder-category :entity="entity"></opportunity-form-builder-category>
        </div>
        <div class="col-6 sm:col-12">
            <div class="form-builder__bg-content form-builder__bg-content--spacing">
                <div v-if="entity.isFirstPhase">
                    <h4><?= i::__("Permitir Agente Coletivo?") ?></h4>
                    <span class="subtitle"><?= i::__("Permitir inscrição de Agente Coletivo") ?></span>
                    <entity-field :entity="entity" prop="useAgentRelationColetivo"></entity-field>
                </div>
                <div v-if="entity.isFirstPhase">
                    <h4><?= i::__("Permitir instituição responsável?") ?></h4>
                    <span class="subtitle"><?= i::__("Permitir inscrição de instituições") ?></span>
                    <entity-field :entity="entity" prop="useAgentRelationInstituicao"></entity-field>
                </div>
                <div>
                    <entity-field :entity="entity" prop="registrationLimit"></entity-field>
                </div>
                <div>
                    <entity-field :entity="entity" prop="registrationLimitPerOwner"></entity-field>
                </div>
            </div>
        </div>
        <div class="col-6 sm:col-12" v-if="entity.isFirstPhase">
            <div class="form-builder__bg-content form-builder__bg-content--spacing">
                <h4><?= i::__("Permitir vínculo de Espaço?") ?></h4>
                <span class="subtitle"><?= i::__("Permitir um espaço para associar à inscrição.") ?></span>
                <entity-field :entity="entity" prop="useSpaceRelationIntituicao"></entity-field>
            </div>
        </div>
        <div class="col-6 sm:col-12">
            <div class="form-builder__bg-content form-builder__bg-content--spacing">
                <h4><?= i::__("Habilitar informações de Projeto?") ?></h4>
                <span class="subtitle"><?= i::__("Permitir que proponente vizualise informações básicas sobre um projeto.") ?></span>
                <entity-field :entity="entity" prop="projectName"></entity-field>
            </div>
        </div>

        <div class="col-12">
            <v1-embed-tool route="formbuilder" :id="entity.id"></v1-embed-tool>
        </div>
    </div>
</div>