app.component('stepper', {
    template: $TEMPLATES['stepper'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('stepper')
        return { text }
    },


    props: {
        steps: {
            type: [Array, Object],
            default: null,
        },
        actualStep: {
            type: Number,
            default: 1,
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
    },

    computed: {
        totalSteps() {
            if (this.steps instanceof Object) {
                return Object.keys(this.steps).length;
            } else if (this.steps instanceof Array) {
                return this.steps.length;
            }
        },
        step () {
            if (this.actualStep >= this.totalSteps) {
                this.actualStep = this.totalSteps;
            } 

            if (this.actualStep <= 1) {
                this.actualStep = 1;
            }

            return this.actualStep;
        },
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
        },
    },
});
