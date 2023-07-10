app.component('mc-stepper', {
    template: $TEMPLATES['mc-stepper'],

    emits: ['stepChanged'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-stepper')
        return { text }
    },

    props: {
        id: {
            type: String,
            default: 'stepper',
        },
        steps: {
            type: [Array, Number],
            default: null,
        },
        onlyActiveLabel: {
            type: Boolean,
            default: false,
        },
        noLabels: {
            type: Boolean,
            default: false
        },
        small: {
            type: Boolean,
            default: false,
        },
        disableNavigation: {
            type: Boolean,
            default: false,
        }
    },

    computed: {
        actualStep() {
            const globalState = useGlobalState();

            if (!globalState[this.id]) {
                globalState[this.id] = 0;
            }

            return globalState[this.id];
        },

        totalSteps() {
            if (typeof this.steps == 'number') {
                return this.steps;
            } else {
                return this.steps.length;
            }
        },

        step() {
            const globalState = useGlobalState();

            let step = document.getElementById( "step" + ((globalState[this.id] ?? 0) + 1) );
            const stepper = document.getElementById( "stepper" );

            if (stepper && stepper.scrollWidth > stepper.clientWidth) {
                if (step.offsetLeft + step.offsetWidth >= stepper.clientWidth) {
                    stepper.parentNode.scrollLeft += step.offsetWidth;
                }
                if (step.offsetLeft + step.offsetWidth <= stepper.clientWidth) {
                    stepper.parentNode.scrollLeft -= step.offsetWidth;
                }
            }

            return globalState[this.id] ?? 0;
        },
    },

    methods: {
        nextStep() {
            this.goToStep(this.actualStep + 1);
        },
        previousStep() {
            this.goToStep(this.actualStep - 1);
        },
        goToStep(step) {
            const globalState = useGlobalState();

            if (step >= this.totalSteps) {
                step = this.totalSteps;
            } else if (step <= 0) {
                step = 0;
            }

            globalState[this.id] = step;

            this.$emit('stepChanged', this.steps[step]);
        },
    },
});
