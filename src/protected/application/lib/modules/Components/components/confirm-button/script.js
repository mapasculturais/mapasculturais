app.component('confirm-button', {
    template: $TEMPLATES['confirm-button'],

    emits: ['confirm', 'cancel'],
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    props: {
        message: String,
        yes: String,
        no: String
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
