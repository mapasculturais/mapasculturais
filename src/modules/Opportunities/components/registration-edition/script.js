app.component('registration-edition', {
    template: $TEMPLATES['registration-edition'],

    props: {
        entity: {
            type: Entity,
            required: true,
        }
    },

    setup() {
        const text = Utils.getTexts('registration-edition');
        return { text }
    },

    data () {
        return {
            stepIndex: 0,
        }
    },

    computed: {
        steps () {
            return this.entity.opportunity.registrationSteps ?? [];
        },

        step () {
            return this.steps[this.stepIndex];
        },

        stepName () {
            if (this.step.name) {
                return this.step.name;
            }

            const fields = [...$MAPAS.config.registrationForm.fields, ...$MAPAS.config.registrationForm.files];
            const field = fields.find((field) => field.step?.id === this.step.id);

            if (field?.step.name) {
                return field.step.name;
            }

            return this.text('Informações básicas');
        },
    },

    methods: {
        nextStep () {
            this.stepIndex++;
        },

        previousStep () {
            this.stepIndex--;
        },
    }
});
