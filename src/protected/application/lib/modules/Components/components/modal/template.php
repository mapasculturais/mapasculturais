<?php

use MapasCulturais\i;
?>
<vue-final-modal v-model="modalOpen" :classes="'modal-container ' + classes" content-class="modal-content">
    <button v-if="closeButton" class="modal__close" @click="close()">X</button>
    <span v-if="title" class="modal__title">{{title}}</span>
    <div class="modal__content">
        <slot :close="close" :open="open" :loading="loading"></slot>
    </div>
    <div class="modal__action">
        <loading :condition="processing"></loading>
        <slot v-if="!processing" name="actions" :close="close" :open="open" :loading="loading"></slot>
    </div>
</vue-final-modal>

<slot name="button" :close="close" :open="open" :loading="loading">
    <button class="button button--sm button--primary" @click="open()">{{button || '<?= i::__('Defina a propriedade `button` do componente modal') ?>'}}</button>
</slot>