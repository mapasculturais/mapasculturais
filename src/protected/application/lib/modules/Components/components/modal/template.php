<vue-final-modal v-model="modalOpen" classes="modal-container" content-class="modal-content">
    <button v-if="closeButton" class="modal__close" @click="close()">X</button>
    <span v-if="title" class="modal__title">{{title}}</span>
    <div class="modal__content">
        <slot :close="close" :open="open"></slot>
    </div>
    <div class="modal__action">
        <slot name="actions" :close="close" :open="open"></slot>
    </div>
</vue-final-modal>

<slot name="button" :close="close" :open="open"></slot>