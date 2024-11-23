<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-modal
    mc-loading
');
?>
<mc-modal classes="modal-confirm" :close-button="false" :title="title">
    <slot v-if="hasSlot('message')" name="message" :cancel="cancel" :confirm="confirm"></slot>
    <div v-if="!hasSlot('message')">{{message}}</div>

    <template #button="modal">
        <slot name="button" :open="modal.open">
            <button class="button" :class="buttonClass" @click="modal.open()"><slot></slot></button>
        </slot>
    </template>

    <template v-if="!loading" #actions="modal">
        <button class="button button--text" @click="cancel(modal)">{{no || "<?php i::_e("Não") ?>"}}</button>
        <button class="button button--primary" @click="confirm(modal)">{{yes || "<?php i::_e("Sim") ?>"}}</button>
    </template>

    <mc-loading :condition="!!loading">{{ loadingMessage }}</mc-loading>
</mc-modal>
