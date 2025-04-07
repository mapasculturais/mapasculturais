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

        disableNavigation: {
            type: Boolean,
            default: false
        }
    },

    setup() {
        const globalState = useGlobalState();
        const text = Utils.getTexts('registration-steps')
        return { globalState, text }
    },

    computed: {
        sections() {
            if (this.steps.length > 1) {
                return this.steps.map((step) => step.name || this.text('Informações básicas'));
            } else {
                return 0;
            }
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
