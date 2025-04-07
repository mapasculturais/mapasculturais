app.component('mc-confirm-button', {
    template: $TEMPLATES['mc-confirm-button'],

    emits: ['confirm', 'cancel'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        const text = Utils.getTexts('mc-confirm-button');
        return { hasSlot, text  }
    },

    props: {
        buttonClass: [String, Array],
        title: String,
        message: String,
        yes: String,
        no: String,
        dontCloseOnConfirm: Boolean,
        loading: [Boolean, String],
    },

    computed: {
        loadingMessage() {
            return this.loading === true ? this.text('processando') : this.loading;
        }
    },

    methods: {
        confirm(modal) {
            this.$emit('confirm', modal);
            if(!this.dontCloseOnConfirm) {
                modal.close();
            }
        },

        cancel(modal) {
            this.$emit('cancel', modal);
            modal.close();
        }
    }
});
