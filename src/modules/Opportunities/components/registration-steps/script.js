app.component('registration-steps', {
    template: $TEMPLATES['registration-steps'],

    emits: ['update:stepIndex'],

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
        const globalState = useGlobalState();
        const text = Utils.getTexts('registration-steps')
        return { globalState, text }
    },

    computed: {
        sections () {
            return this.steps.map((step) => step.name || this.text('Informações básicas'));
        },
    },

    watch: {
        stepIndex () {
            this.globalState.stepper = this.stepIndex;
        },
    },

    methods: {
        goToSection(event) {
            this.$emit('update:stepIndex', event);
        },
    },
});
