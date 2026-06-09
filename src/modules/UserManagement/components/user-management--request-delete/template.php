<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-alert
    mc-confirm-button
    mc-icon
    mc-modal
');
?>
<mc-confirm-button
    @confirm="openRequestModal"
    button-class="button--text delete button--icon button--sm panel__entity-actions--trash">
    <mc-icon name="trash"></mc-icon>
    <span><?php i::_e('Excluir') ?></span>

    <template #message="{ cancel, confirm }">
        <div class="user-management__request-delete-info">
            <p>{{text('lgpdNotice')}}</p>
            <p>{{text('confirmQuestion')}}</p>
        </div>
    </template>
</mc-confirm-button>

<mc-modal
    ref="requestModal"
    classes="user-management__request-delete-modal"
    :subtitle="text('modalSubtitle')"
    title="<?php i::esc_attr_e('Solicitar exclusão da conta') ?>">
    <template #default="modal">
        <div class="user-management__request-delete">
            <mc-alert type="helper" class="user-management__request-delete-alert">
                {{text('modalIntro')}}
            </mc-alert>

            <div class="field user-management__request-delete-field">
                <label>{{text('messageLabel')}}</label>
                <textarea
                    v-model="requestMessage"
                    rows="8"
                    class="user-management__request-delete-textarea"
                    :disabled="processing"></textarea>
                <small class="user-management__request-delete-help">{{text('messageHelp')}}</small>
            </div>

            <div class="user-management__request-delete-copy-box">
                <div class="field user-management__request-delete-copy">
                    <label class="user-management__request-delete-checkbox">
                        <input type="checkbox" v-model="sendCopy" :disabled="processing">
                        <span>{{text('copyLabel')}}</span>
                    </label>
                </div>

                <div class="field user-management__request-delete-copy-email" v-if="sendCopy">
                    <label>{{text('copyEmailLabel')}}</label>
                    <input
                        type="email"
                        v-model="copyEmail"
                        :placeholder="entity.email"
                        :disabled="processing">
                </div>
            </div>
        </div>
    </template>

    <template #actions="modal">
        <button
            class="button button--primary button--md"
            :disabled="processing || !requestMessage.trim()"
            @click="submit(modal)">
            <span v-if="!processing"><?php i::_e('Enviar solicitação') ?></span>
            <span v-if="processing"><?php i::_e('Enviando...') ?></span>
        </button>
        <button
            class="button button--text button--md"
            :disabled="processing"
            @click="modal.close()"><?php i::_e('Cancelar') ?></button>
    </template>
</mc-modal>
