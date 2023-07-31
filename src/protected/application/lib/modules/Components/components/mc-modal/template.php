<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-loading
');
?>
<vue-final-modal v-model="modalOpen" :attach="teleport" :classes="['modal-container',classes]" content-class="modal-content" transition="modal" modalPosition="fixed" :modalClasses="['modal-fixed']" esc-to-close>
    <template v-if="modalOpen">
        <div class="modal__header">
            <span v-if="title" class="modal__title">{{title}}</span>
            <button v-if="closeButton" class="modal__close" @click="close()"> <mc-icon name="close"></mc-icon> </button>
        </div>
        <div class="modal__content">
            <slot :close="close" :open="open" :isOpen="modalOpen" :toggle="toggle" :loading="loading"></slot>
        </div>
        <div class="modal__action">
            <mc-loading :condition="processing"></mc-loading>
            <slot v-if="!processing" name="actions" :close="close" :open="open" :toggle="toggle" :loading="loading"></slot>
        </div>
    </template>
</vue-final-modal>

<slot name="button" :close="close" :open="open" :toggle="toggle" :loading="loading">
    <button :class="['button',buttonClasses]" @click="open()">{{buttonLabel || '<?= i::__('Defina a propriedade `buttonLabel` do componente modal') ?>'}}</button>
</slot>