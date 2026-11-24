app.component('opportunity-reports-filters', {
    template: $TEMPLATES['opportunity-reports-filters'],

    props: {
        modelValue: {
            type: Object,
            required: true, // { status }
        },
    },

    emits: ['update:modelValue'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-reports-filters');
        return { text };
    },

    computed: {
        statusOptions() {
            return [
                { value: 'all', label: this.text('status_all') },
                { value: 'draft', label: this.text('status_draft') },
                { value: 'send', label: this.text('status_send') },
                { value: 'invalid', label: this.text('status_invalid') },
                { value: 'notapproved', label: this.text('status_notapproved') },
                { value: 'waitlist', label: this.text('status_waitlist') },
                { value: 'approved', label: this.text('status_approved') },
            ];
        },
    },

    methods: {
        onStatusChange(status) {
            this.$emit('update:modelValue', { ...this.modelValue, status });
        },
    },
});
