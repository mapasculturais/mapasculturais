<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    oc-dialog
    oc-popover
    mc-icon
 ');

?>

<div class="settings-email">
    <oc-dialog>
        <template #content>
            <?= i::__('Configure aqui as credenciais SMTP para o envio de e-mails automáticos, como criação de conta, redefinição de senha e notificações.') ?>
            <?= i::__('Será necessário informar e-mail, porta, protocolo e senha. Consulte o responsável de TI para obter esses dados.') ?>
        </template>
    </oc-dialog>
    <div class="grid-12">
        <entity-field :entity="entity" prop="mailer_email" classes="col-12"></entity-field>
        <entity-field :entity="entity" prop="mailer_host" classes="col-4"></entity-field>
        <entity-field :entity="entity" prop="mailer_user" classes="col-4"></entity-field>
        <entity-field :entity="entity" prop="mailer_protocol" classes="col-4"></entity-field>
        <entity-field :entity="entity" prop="mailer_password" classes="col-6"></entity-field>
        <entity-field :entity="entity" prop="mailer_repassword" classes="col-6"></entity-field>
        <div class="col-12 email-test">
            <oc-popover position="left">
                <template #content="{popover}">
                    <mc-loading :condition="isLoading">
                        <template #default="{ entity }">
                            <?= i::__('Enviando e-mail, aguarde') ?>
                        </template>
                    </mc-loading>
                    <mc-icon name="one-click-close-rounded" @click="toggle(popover, emailTest)"></mc-icon>
                    <div v-if="!isLoading" class="field">
                        <input v-model="emailTest" type="text" class="" autocomplete="off" value="">
                    </div>
                    <button v-if="!isLoading" class="button button--primary" :class="{'disabled' : !emailTest}" @click="sendEmailTest()"><span><?= i::__('Enviar') ?></span></button>
                </template>
            </oc-popover>
        </div>
    </div>


    <div class="btn-entity-actions">
        <oc-actions :entity="entity" editable></oc-actions>
    </div>
</div>