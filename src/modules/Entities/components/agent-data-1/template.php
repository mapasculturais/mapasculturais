<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    entity-data
');
?>
<div class="col-12 agent-data">
    <template v-if="verifyFields()">
        <div class="agent-data__title">
            <h4 class="title bold" v-if="alwaysShowTitle || (entity.telefonePublico || entity.emailPublico)"><?php i::_e("Dados Pessoais") ?>
                <?php if($this->isEditable()): ?>
                    <?php $this->info('cadastro -> configuracoes-entidades -> dados-pessoais') ?>
                <?php endif; ?>
            </h4>
        </div>
        <div class="agent-data__fields">
            <entity-data v-if="entity.nomeCompleto && !showOnlyPublicData" class="agent-data__fields--field" :entity="entity" prop="nomeCompleto" label="<?php i::_e("Nome Completo")?>"></entity-data>
            <entity-data v-if="entity.nomeSocial && !showOnlyPublicData" class="agent-data__fields--field" :entity="entity" prop="nomeSocial" label="<?php i::_e("Nome Social")?>"></entity-data>
            <entity-data v-if="entity.cpf && !showOnlyPublicData" class="agent-data__fields--field" :entity="entity" prop="cpf" label="<?php i::_e("CPF")?>"></entity-data>
            <entity-data v-if="entity.cnpj && !showOnlyPublicData" class="agent-data__fields--field" :entity="entity" prop="cnpj" label="<?php i::_e("MEI (CNPJ do MEI)")?>"></entity-data>
            <entity-data v-if="entity.telefonePublico && !showOnlyPublicData" class="agent-data__fields--field" :entity="entity" prop="telefonePublico" label="<?php i::_e("Telefone Público")?>"></entity-data>
            <entity-data v-if="entity.telefone1 && !showOnlyPublicData" class="agent-data__fields--field" :entity="entity" prop="telefone1" label="<?php i::_e("Telefone Privado 1")?>"></entity-data>
            <entity-data v-if="entity.telefone2 && !showOnlyPublicData" class="agent-data__fields--field" :entity="entity" prop="telefone2" label="<?php i::_e("Telefone Privado 2")?>"></entity-data>
            <entity-data v-if="entity.emailPrivado && !showOnlyPublicData" class="agent-data__fields--field" :entity="entity" prop="emailPrivado" label="<?php i::_e("Email Pessoal")?>"></entity-data>
            <entity-data v-if="entity.emailPublico && !showOnlyPublicData" class="agent-data__fields--field" :entity="entity" prop="emailPublico" label="<?php i::_e("Email Público")?>"></entity-data>
        </div>
    </template>
    <template v-if="verifySensitiveFields() && entity.currentUserPermissions.viewPrivateData && !showOnlyPublicData">
        <div class="agent-data__secondTitle">
            <h4 class="title bold"><?php i::_e("Dados pessoais sensíveis") ?>
                <?php if($this->isEditable()): ?>
                    <?php $this->info('cadastro -> configuracoes-entidades -> dados-pessoais-sensiveis') ?>
                <?php endif; ?>
            </h4>
        </div>
        <div class="agent-data__fields">
            <entity-data v-if="entity.dataDeNascimento" class="agent-data__fields--field" :entity="entity" prop="dataDeNascimento" label="<?php i::_e("Data de Nascimento")?>"></entity-data>
            <div v-if="entity.dataDeNascimento" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Pessoa idosa") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.idoso ? 'Sim' : 'Não'}}</label>
                </div>
            </div>
            <entity-data v-if="entity.genero" class="agent-data__fields--field" :entity="entity" prop="genero" label="<?php i::_e("Gênero")?>"></entity-data>
            <entity-data v-if="entity.orientacaoSexual" class="agent-data__fields--field" :entity="entity" prop="orientacaoSexual" label="<?php i::_e("Orientação Sexual")?>"></entity-data>
            <entity-data v-if="entity.agenteItinerante" class="agent-data__fields--field" :entity="entity" prop="agenteItinerante" label="<?php i::_e("Agente Itinerante")?>"></entity-data>
            <entity-data v-if="entity.raca" class="agent-data__fields--field" :entity="entity" prop="agenteItinerante" label="<?php i::_e("Raça/Cor") ?>"></entity-data>
            <entity-data v-if="entity.escolaridade" class="agent-data__fields--field" :entity="entity" prop="escolaridade" label="<?php i::_e("Escolaridade") ?>"></entity-data>
            <entity-data v-if="entity.pessoaDeficiente" class="agent-data__fields--field" :entity="entity" prop="pessoaDeficiente" label="<?php i::_e("Pessoa portadora de deficiência") ?>"></entity-data>
            <entity-data v-if="entity.pessoaDeficiente.length==1 && entity.pessoaDeficiente[0]==''" class="agent-data__fields--field" :entity="entity" prop="pessoaDeficiente" label="<?php i::_e("Não sou") ?>"></entity-data>
            <entity-data v-if="entity.comunidadesTradicional" class="agent-data__fields--field" :entity="entity" prop="comunidadesTradicional" label="<?php i::_e("Comunidades Tradicionais") ?>"></entity-data>
            <entity-data v-if="entity.comunidadesTradicionalOutros" class="agent-data__fields--field" :entity="entity" prop="comunidadesTradicionalOutros" label="<?php i::_e("Outras Comunidades Tradicionais") ?>"></entity-data>
        </div>
    </template>
</div>