<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
');
?>
<div class="col-12 agent-data">
    <template v-if="verifyFields()">
        <div class="agent-data__title">
            <h4 class="title bold"><?php i::_e("Dados Pessoais") ?>
                <?php if($this->isEditable()): ?>
                    <?php $this->info('cadastro -> configuracoes-entidades -> dados-pessoais') ?>
                <?php endif; ?>
            </h4>
        </div>
        <div class="agent-data__fields">
            <div v-if="entity.nomeCompleto" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Nome Completo") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.nomeCompleto}}</label>
                </div>
            </div>
            <div v-if="entity.nomeSocial" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Nome Social") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.nomeSocial}}</label>
                </div>
            </div>
            <div v-if="entity.cpf" class="agent-data__fields--field">
                <label class="title"><?php i::_e("CPF") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.cpf}}</label>
                </div>
            </div>
            <div v-if="entity.cnpj" class="agent-data__fields--field">
                <label class="title"><?php i::_e("MEI (CNPJ do MEI)") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.cnpj}}</label>
                </div>
            </div>
            <div v-if="entity.telefonePublico" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Telefone Público") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.telefonePublico}}</label>
                </div>
            </div>
            <div v-if="entity.telefone1" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Telefone Privado 1") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.telefone1}}</label>
                </div>
            </div>
            <div v-if="entity.telefone2" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Telefone Privado 2") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.telefone2}}</label>
                </div>
            </div>
            <div v-if="entity.emailPrivado" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Email Pessoal") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.emailPrivado}}</label>
                </div>
            </div>
            <div v-if="entity.emailPublico" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Email Púbico") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.emailPublico}}</label>
                </div>
            </div>
        </div>
    </template>
    <template v-if="verifySensitiveFields() && entity.currentUserPermissions.viewPrivateData">
        <div class="agent-data__secondTitle">
            <h4 class="title bold"><?php i::_e("Dados pessoais sensíveis") ?>
                <?php if($this->isEditable()): ?>
                    <?php $this->info('cadastro -> configuracoes-entidades -> dados-pessoais-sensiveis') ?>
                <?php endif; ?>
            </h4>
        </div>
        <div class="agent-data__fields">
            <div v-if="entity.dataDeNascimento" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Data de Nascimento") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.dataDeNascimento.date('long year')}}</label>
                </div>
            </div>
            <div v-if="entity.dataDeNascimento" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Pessoa idosa") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.idoso ? 'Sim' : 'Não'}}</label>
                </div>
            </div>
            <div v-if="entity.genero" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Gênero") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.genero}}</label>
                </div>
            </div>
            <div v-if="entity.orientacaoSexual" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Orientação Sexual") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.orientacaoSexual}}</label>
                </div>
            </div>
            <div v-if="entity.agenteItinerante" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Agente Itinerante") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.agenteItinerante}}</label>
                </div>
            </div>
            <div v-if="entity.raca" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Raça/Cor") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.raca}}</label>
                </div>
            </div>
            <div v-if="entity.escolaridade" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Escolaridade") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.escolaridade}}</label>
                </div>
            </div>
            <div v-if="entity.pessoaDeficiente" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Pessoa portadora de deficiência") ?></label>
                <div class="box">
                    <label v-if="entity.pessoaDeficiente" class="box__content">{{entity.pessoaDeficiente?.join(", ")}}</label>
                    <label v-if="entity.pessoaDeficiente.length==1 && entity.pessoaDeficiente[0]==''" class="box__content"><?= i::__("Não sou") ?></label>
                </div>
            </div>
            <div v-if="entity.comunidadesTradicional" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Comunidades Tradicionais") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.comunidadesTradicional}}</label>
                </div>
            </div>
            <div v-if="entity.comunidadesTradicionalOutros" class="agent-data__fields--field">
                <label class="title"><?php i::_e("Outras Comunidades Tradicionais") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.comunidadesTradicionalOutros}}</label>
                </div>
            </div>
        </div>
    </template>
</div>