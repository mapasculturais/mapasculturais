app.component('stepper', {
    template: $TEMPLATES['stepper'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('stepper')
        return { text }
    },


    props: {
        totalSteps: {
            type: Number,
            required: true
        },
        stepped: {
            type: Number,
            default: 1
        },
        small: {
            type: Boolean,
            default: false
        },
        actualLabel: {
            type: String,
            default: null
        },
        lastLabel: {
            type: String,
            default: null
        },
        
        /* for debugging purposes */
        showButtons: {
            type: Boolean,
            required: false
        }
    },

    data() {
        return {
            actualStep: this.stepped
        }
    },

    computed: {
        step () {
            if (this.actualStep >= this.totalSteps) {
                this.actualStep = this.totalSteps;
            } 

            if (this.actualStep <= 1) {
                this.actualStep = 1;
            }

            return this.actualStep;
        }
    },
    
    methods: {
        nextStep() {
            ++this.actualStep;
        },
        previousStep() {
            --this.actualStep;
        },
        goToStep(step) {
            this.actualStep = step;
        }
    },
});
