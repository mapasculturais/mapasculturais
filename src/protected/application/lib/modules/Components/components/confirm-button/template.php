<?php
use MapasCulturais\i;
$this->import('modal');
?>
<modal classes="modal-confirm" :close-button="false">
    <slot v-if="hasSlot('message')" name="message" :cancel="cancel" :confirm="confirm"></slot>
    <div v-if="!hasSlot('message')">{{message}}</div>

    <template #button="modal">
        <slot name="button" :open="modal.open">
            <button class="button" :class="buttonClass" @click="modal.open()"><slot></slot></button>
        </slot>
    </template>

    <template #actions="modal">
        <button class="button button--text" @click="cancel(modal)">{{no || "<?php i::_e("Não") ?>"}}</button>
        <button class="button button--primary" @click="confirm(modal)">{{yes || "<?php i::_e("Sim") ?>"}}</button>
    </template>
</modal>
