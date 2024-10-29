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
        currentStep () {
            return this.steps[this.stepIndex];
        },

        steps () {
            return this.entity.opportunity.registrationSteps ?? [];
        },
    },
});
