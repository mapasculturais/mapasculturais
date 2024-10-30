app.component('registration-steps', {
    template: $TEMPLATES['registration-steps'],

    emits: ['stepChanged'],

    props: {
        steps: {
            type: Array,
            required: true,
        },

        stepIndex: {
            type: Number,
            default: 0,
        },
    },

    setup() {
        const text = Utils.getTexts('registration-steps')
        return { text }
    },
    
    computed: {
        sections () {
            return this.steps.map((step) => step.name || this.text('Informações básicas'));
        },
    },

    methods: {
        goToSection(event) {
            this.$emit('stepChanged', event);
        },
    },
});
