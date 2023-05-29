app.component('mc-confirm-button', {
    template: $TEMPLATES['mc-confirm-button'],

    emits: ['confirm', 'cancel'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    props: {
        buttonClass: [String, Array],
        title: String,
        message: String,
        yes: String,
        no: String,
    },

    methods: {
        confirm(modal) {
            this.$emit('confirm', modal);
            modal.close();
        },

        cancel(modal) {
            this.$emit('cancel', modal);
            modal.close();
        }
    }
});
