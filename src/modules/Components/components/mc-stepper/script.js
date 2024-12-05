app.component('mc-stepper', {
    template: $TEMPLATES['mc-stepper'],

    emits: ['stepChanged'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-stepper')
        return { text }
    },

    props: {
        steps: {
            type: [Array, Number],
            default: null,
        },
        step: {
            type: Number,
            default: 0,
        },
        onlyActiveLabel: {
            type: Boolean,
            default: false,
        },
        noLabels: {
            type: Boolean,
            default: false
        },
        disableNavigation: {
            type: Boolean,
            default: false,
        },
        disabledSteps: {
            type: Array,
            default: () => [],
        },
    },

    methods: {
        goToStep(step) {
            this.$emit('stepChanged', step);
        },
    },
});
