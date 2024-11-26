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

    watch: {
        stepIndex() {
            window.location.hash = `#etapa_${this.stepIndex + 1}`;
        },
    },

    beforeMount() {
        const hash = window.location.hash;
        if (hash) {
            const matches = hash.match(/^#etapa_([0-9]+)$/);
            if (matches?.[1]) {
                const stepIndex = parseInt(matches[1]);
                if (stepIndex >= 1 && stepIndex <= this.steps.length) {
                    this.stepIndex = stepIndex - 1;
                }
            }
        }
    },
});
