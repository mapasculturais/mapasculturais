<?php

use MapasCulturais\i;

$this->import('
mapas-card
');
?>
<div class="col-12 agent-data">

    <div v-if="entity.currentUserPermissions.viewPrivateData && verifyEntity()" class="agent-data__title">
        <h4 class="title">{{title}}</h4>
    </div>
    <div v-if="entity.name?.length>0 && entity.currentUserPermissions.viewPrivateData" class="agent-data__fields">
        <div class="agent-data__fields--field">
            <label class="title"><?php i::_e("Nome Fantasia ou razão social") ?></label>
            <div class="box">
                <label class="box__content">{{entity.name}}</label>
            </div>
        </div>
      
        <div v-if="entity.cpf?.length>0" class="agent-data__fields--field">
            <label class="title"><?php i::_e("CPF") ?></label>
            <div class="box">
                <label class="box__content">{{entity.cpf}}</label>
            </div>
        </div>
        <div v-if="entity.cnpj?.length>0" class="agent-data__fields--field">
            <label class="title"><?php i::_e("MEI") ?></label>
            <div class="box">
                <label class="box__content">{{entity.cnpj}}</label>
            </div>
        </div>
        
        <div v-if="entity.telefonePublico?.length>0" class="agent-data__fields--field">
            <label class="title"><?php i::_e("Telefone Público") ?></label>
            <div class="box">
                <label class="box__content">{{entity.telefonePublico}}</label>
            </div>
        </div>
        <div v-if="entity.telefonePublico?.length>0" class="agent-data__fields--field">
            <label class="title"><?php i::_e("Telefone Público 1") ?></label>
            <div class="box">
                <label class="box__content">{{entity.telefone1}}</label>
            </div>
        </div>
        <div v-if="entity.telefonePublico?.length>0" class="agent-data__fields--field">
            <label class="title"><?php i::_e("Telefone Público 2") ?></label>
            <div class="box">
                <label class="box__content">{{entity.telefone2}}</label>
            </div>
        </div>
        <div v-if="entity.emailPrivado?.length>0" class="agent-data__fields--field">
            <label class="title"><?php i::_e("Email Pessoal") ?></label>
            <div class="box">
                <label class="box__content">{{entity.emailPrivado}}</label>
            </div>
        </div>
        <div v-if="entity.emailPublico?.length>0" class="agent-data__fields--field">
            <label class="title"><?php i::_e("Email Público") ?></label>
            <div class="box">
                <label class="box__content">{{entity.emailPublico}}</label>
            </div>
        </div>
    </div>
</div>