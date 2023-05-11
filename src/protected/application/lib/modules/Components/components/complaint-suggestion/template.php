<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import("mapas-card modal");
?>
<div class="complaint-suggestion">
    <div class="complaint-sugestion__complaint">
        <modal title="<?= i::__('Denúncia') ?>">
            <div class="content">
                <div v-if="!formData.anonimous" class="content__name field">
                    <label><?= i::__('Nome') ?></label>
                    <input type="text" v-model="formData.name">
                </div>
                <div v-if="!formData.anonimous" class="content__email field">
                    <label><?= i::__('E-mail') ?></label>
                    <input type="text" v-model="formData.email">
                </div>
                <div class="content__type field">
                    <label><?= i::__('Tipo') ?></label>
                    <select v-model="formData.type">
                        <option value=""><?= i::__('Selecione') ?></option>
                        <option v-for="(item,index) in options.complaint" v-bind:value="item">{{item}}</option>
                    </select>
                </div>
                <div class="content__message field">
                    <label><?= i::__('Mensagem') ?></label>
                    <textarea v-model="formData.message"></textarea>
                </div>
                <div class="content__inputs field">
                    <label> <input type="checkbox" v-model="formData.anonimous"><?= i::__('Denúncia anônima') ?></label>
                    <label><input type="checkbox" v-model="formData.copy"><?= i::__('Receber copia da denúncia') ?></label>
                </div>

            </div>
            <template #actions="modal">
                <VueRecaptcha v-if="sitekey" :sitekey="sitekey" @verify="verifyCaptcha" @expired="expiredCaptcha" @render="expiredCaptcha" class="g-recaptcha col-12"></VueRecaptcha>
                <button class="button button--primary" @click="send(modal)"><?= i::__('Enviar Denúncia') ?></button>
                <button class="button button--text button--text-del" @click="modal.close()"><?= i::__('cancelar') ?></button>
            </template>

            <template #button="modal">
                <button type="button" @click="modal.open();typeMessage='sendComplaintMessage'; initFormData()" class="button button--primary-outline"><?= i::__('Denúncia') ?></button>
            </template>
        </modal>
    </div>
    <div class="complaint-sugestion__suggestion">
        <modal title="<?= i::__('Contato') ?>">
            <div class="content">
                <div v-if="!formData.anonimous" class="content__name field">
                    <label><?= i::__('Nome') ?></label>
                    <input type="text" v-model="formData.name">
                </div>
                <div v-if="!formData.anonimous" class="content__email field">
                    <label><?= i::__('E-mail') ?></label>
                    <input type="text" v-model="formData.email">
                </div>
                <div class="content__type field">
                    <label><?= i::__('Tipo') ?></label>
                    <select v-model="formData.type">
                        <option value=""><?= i::__('Selecione') ?></option>
                        <option v-for="(item,index) in options.suggestion" v-bind:value="item">{{item}}</option>
                    </select>
                </div>
                <div class="content__message field">
                    <label><?= i::__('Mensagem:') ?></label>
                    <textarea v-model="formData.message"></textarea>
                </div>
                <div class="content__inputs field">
                    <label> <input type="checkbox" v-model="formData.anonimous"><?= i::__('Mensagem anônima') ?></label>
                    <label><input type="checkbox" v-model="formData.only_owner"><?= i::__('Enviar somente para o responsável') ?></label>
                    <label><input type="checkbox" v-model="formData.copy"><?= i::__('Receber copia da mensagem') ?></label>
                </div>
            </div>

            <template #actions="modal">
                <VueRecaptcha v-if="sitekey" :sitekey="sitekey" @verify="verifyCaptcha" @expired="expiredCaptcha" @render="expiredCaptcha" class="g-recaptcha col-12"></VueRecaptcha>
                <button class="button button--primary" @click="send(modal)"><?= i::__('Enviar Mensagem') ?></button>
                <button class="button button--text button--text-del" @click="modal.close()"><?= i::__('Cancelar') ?></button>
            </template>

            <template #button="modal">
                <button type="button" @click="modal.open();typeMessage='sendSuggestionMessage';initFormData()" class="button button--primary"><?= i::__('Contato') ?></button>
            </template>
        </modal>
    </div>
</div>