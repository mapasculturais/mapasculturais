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
            const steps = this.entity.opportunity.registrationSteps ?? [];
            return steps.sort((a, b) => a.displayOrder - b.displayOrder);
        },

        step () {
            return this.steps[this.stepIndex];
        },
    },
});
