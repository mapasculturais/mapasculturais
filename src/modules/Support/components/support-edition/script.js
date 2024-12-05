app.component('support-edition', {
    template: $TEMPLATES['support-edition'],

    props: {
        registration: {
            type: Entity,
            required: true,
        },
    },

    data () {
        return {
            stepIndex: 0,
        };
    },

    computed: {
        disabledSteps () {
            const presentSteps = new Set();
            const disabledIndexes = [];

            const fields = [...$MAPAS.config.registrationForm.fields, ...$MAPAS.config.registrationForm.files];
            for (const field of fields) {
                if (field.step) {
                    presentSteps.add(field.step.id);
                }
            }

            this.steps.forEach((step, stepIndex) => {
                if (!presentSteps.has(step.id)) {
                    disabledIndexes.push(stepIndex);
                }
            });

            return disabledIndexes;
        },

        steps () {
            const steps = this.registration.opportunity.registrationSteps ?? [];
            const { category, proponentType, range } = this.registration;

            const filteredSteps = steps.filter((step) => {
                const conditional = step.metadata?.conditional;
                if (conditional) {
                    if (conditional.categories) {
                        if (category && !conditional.categories.includes(category)) {
                            return false;
                        }
                    }
                    if (conditional.proponentTypes) {
                        if (proponentType && !conditional.proponentTypes.includes(proponentType)) {
                            return false;
                        }
                    }
                    if (conditional.ranges) {
                        if (range && !conditional.ranges.includes(range)) {
                            return false;
                        }
                    }
                }
                return true;
            });

            return filteredSteps.sort((a, b) => a.displayOrder - b.displayOrder);
        },

        step () {
            return this.steps[this.stepIndex];
        },
    },
});