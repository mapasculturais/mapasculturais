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
    <template v-if="entity.currentUserPermissions.viewPrivateData && verifyEntity()">
        <div v-if="entity.name" class="agent-data__fields">
            <div class="agent-data__fields--field">
                <label class="title"><?php i::_e("Nome Fantasia ou razão social") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.name}}</label>
                </div>
            </div>

            <div v-if="entity.cpf" class="agent-data__fields--field">
                <label class="title"><?php i::_e("CPF") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.cpf}}</label>
                </div>
            </div>
            <div v-if="entity.cnpj" class="agent-data__fields--field">
                <label class="title"><?php i::_e("CNPJ") ?></label>
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
                <label class="title"><?php i::_e("Email Público") ?></label>
                <div class="box">
                    <label class="box__content">{{entity.emailPublico}}</label>
                </div>
            </div>
        </div>
    </template>
</div>