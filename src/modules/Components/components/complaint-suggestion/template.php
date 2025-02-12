<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import(" 
    mc-modal
    mc-captcha
");
?>
<div class="complaint-suggestion col-12">
    <div class="complaint-sugestion__complaint">
        <mc-modal title="<?= i::__('Denúncia') ?>" classes="complaint-sugestion__modal">

            <div class="complaint-suggestion__modal-content">
                <div class="complaint-suggestion__input-group">
                    <div class="field">
                        <label>
                            <input type="checkbox" v-model="formData.anonimous" @click="formData.copy = false;"><?= i::__('Enviar a denúncia de forma anônima') ?>
                        </label>
                    </div>
                </div>

                <div v-if="!formData.anonimous" class="field">
                    <label><?= i::__('Nome') ?></label>
                    <input type="text" v-model="formData.name">
                </div>

                <div v-if="!formData.anonimous || formData.copy" class="field">
                    <label><?= i::__('E-mail') ?></label>
                    <input type="text" v-model="formData.email">
                </div>

                <div class="field">
                    <label><?= i::__('Tipo') ?></label>
                    <select v-model="formData.type">
                        <option value=""><?= i::__('Selecione') ?></option>
                        <option v-for="(item,index) in options.complaint" v-bind:value="item">{{item}}</option>
                    </select>
                </div>

                <div class="field">
                    <label><?= i::__('Mensagem') ?></label>
                    <textarea v-model="formData.message"></textarea>
                </div>

                <div class="complaint-suggestion__input-group">
                    <div :class="['field', {'disabled':formData.anonimous}]">
                        <label>
                            <input type="checkbox" :disabled="formData.anonimous" v-model="formData.copy"><?= i::__('Receber copia da mensagem') ?>
                        </label>
                    </div>
                </div>
            </div>
            
            <template #actions="modal">
                <!-- Componente responsável por renderizar o CAPTCHA -->
                <mc-captcha @captcha-verified="verifyCaptcha" @captcha-expired="expiredCaptcha" class="col-12"></mc-captcha>

                <button class="button button--primary" @click="send(modal)"><?= i::__('Enviar Denúncia') ?></button>
                <button class="button button--text button--text-del" @click="modal.close()"><?= i::__('cancelar') ?></button>
            </template>

            <template #button="modal">
                <button type="button" @click="modal.open(); initFormData('sendComplaintMessage')" class="button button--primary-outline"><?= i::__('Denúncia') ?></button>
            </template>
        </mc-modal>
    </div>

    <div class="complaint-suggestion__suggestion">
        <mc-modal title="<?= i::__('Contato') ?>" classes="complaint-sugestion__modal">

            <div class="complaint-suggestion__modal-content">

                <div class="complaint-suggestion__input-group">
                    <div class="field">
                        <label>
                            <input type="checkbox" v-model="formData.anonimous" @click="formData.copy = false;"><?= i::__('Enviar a mensagem de forma anônima') ?>
                        </label>
                    </div>
                </div>

                <div v-if="!formData.anonimous" class="field">
                    <label><?= i::__('Nome') ?></label>
                    <input type="text" v-model="formData.name">
                </div>

                <div v-if="!formData.anonimous || formData.copy" class="field">
                    <label><?= i::__('E-mail') ?></label>
                    <input type="text" v-model="formData.email">
                </div>

                <div class="field">
                    <label><?= i::__('Tipo') ?></label>
                    <select v-model="formData.type">
                        <option value=""><?= i::__('Selecione') ?></option>
                        <option v-for="(item,index) in options.suggestion" v-bind:value="item">{{item}}</option>
                    </select>
                </div>

                <div class="field">
                    <label><?= i::__('Mensagem:') ?></label>
                    <textarea v-model="formData.message"></textarea>
                </div>

                <div class="complaint-suggestion__input-group">
                    <div class="field">
                        <label>
                            <input type="checkbox" v-model="formData.only_owner"><?= i::__('Enviar somente para o responsável') ?>
                        </label>
                    </div>

                    <div :class="['field', {'disabled':formData.anonimous}]">
                        <label>
                            <input type="checkbox" :disabled="formData.anonimous" v-model="formData.copy"><?= i::__('Receber copia da mensagem') ?>
                        </label>
                    </div>
                </div>
            </div>

            <template #actions="modal">
                <!-- Componente responsável por renderizar o CAPTCHA -->
                <mc-captcha class="complaint-suggestion__recaptcha" @captcha-verified="verifyCaptcha" @captcha-expired="expiredCaptcha"></mc-captcha>

                <button class="button button--primary" @click="send(modal)"><?= i::__('Enviar Mensagem') ?></button>
                <button class="button button--text button--text-del" @click="modal.close()"><?= i::__('Cancelar') ?></button>
            </template>

            <template #button="modal">
                <button type="button" @click="modal.open(); initFormData('sendSuggestionMessage')" class="button button--primary"><?= i::__('Contato') ?></button>
            </template>
        </mc-modal>
    </div>
</div>