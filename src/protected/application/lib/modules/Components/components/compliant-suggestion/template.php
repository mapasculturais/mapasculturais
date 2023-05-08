<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import("mapas-card modal");
?>
<div>
    <div>
        <modal title="<?= i::__('Modal de denuncia') ?>">
            <div>
                <div>
                    <label><?= i::__('Nome:') ?></label>
                    <input type="text" v-model="formData.name">
                </div>
                <div>
                    <label><?= i::__('E-mail:') ?></label>
                    <input type="text" v-model="formData.email">
                </div>
                <div>
                    <label><?= i::__('Tipo:') ?></label>
                    <select v-model="formData.type">
                        <option value=""><?= i::__('Selecione') ?></option>
                        <option v-for="(item,index) in options.compliant" v-bind:value="item">{{item}}</option>
                    </select>
                </div>
                <div>
                    <label><?= i::__('Mensagem:') ?></label>
                    <textarea v-model="formData.message"></textarea>
                </div>
                <div>
                    <label> <input type="checkbox" v-model="formData.anonimous"><?= i::__('Denúncia anônima') ?></label>
                    <label><input type="checkbox" v-model="formData.copy"><?= i::__('Receber copia da denúncia') ?></label>
                </div>

            </div>
            <template #actions="modal">
                <VueRecaptcha v-if="sitekey" :sitekey="sitekey" @verify="verifyCaptcha" @expired="expiredCaptcha" @render="expiredCaptcha" class="g-recaptcha col-12"></VueRecaptcha>
                <button @click="send(modal)"><?= i::__('Enviar Denúncia') ?></button>
                <button @click="modal.close()"><?= i::__('cancelar') ?></button>
            </template>

            <template #button="modal">
                <button type="button" @click="modal.open();typeMessage='sendCompliantMessage'; initFormData()" class="button"><?= i::__('Denúncia') ?></button>
            </template>
        </modal>
    </div>
    <div>
        <modal title="<?= i::__('Modal de Contato') ?>">
            <div>
                <div>
                    <label><?= i::__('Nome:') ?></label>
                    <input type="text" v-model="formData.name">
                </div>
                <div>
                    <label><?= i::__('E-mail:') ?></label>
                    <input type="text" v-model="formData.email">
                </div>
                <div>
                    <label><?= i::__('Tipo:') ?></label>
                    <select v-model="formData.type">
                        <option value=""><?= i::__('Selecione') ?></option>
                        <option v-for="(item,index) in options.suggestion" v-bind:value="item">{{item}}</option>
                    </select>
                </div>
                <div>
                    <label><?= i::__('Mensagem:') ?></label>
                    <textarea v-model="formData.message"></textarea>
                </div>
                <div>
                    <label> <input type="checkbox" v-model="formData.anonimous"><?= i::__('Mensagem anônima') ?></label>
                    <label><input type="checkbox" v-model="formData.only_owner"><?= i::__('Enviar somente para o responsável') ?></label>
                    <label><input type="checkbox" v-model="formData.copy"><?= i::__('Receber copia da mensagem') ?></label>
                </div>
            </div>

            <template #actions="modal">
                <VueRecaptcha v-if="sitekey" :sitekey="sitekey" @verify="verifyCaptcha" @expired="expiredCaptcha" @render="expiredCaptcha" class="g-recaptcha col-12"></VueRecaptcha>
                <button @click="send"><?= i::__('Enviar Mensagem') ?></button>
                <button @click="modal.close()"><?= i::__('Cancelar') ?></button>
            </template>

            <template #button="modal">
                <button type="button" @click="modal.open();typeMessage='sendSuggestionMessage';initFormData()" class="button"><?= i::__('Contato') ?></button>
            </template>
        </modal>
    </div>
</div>