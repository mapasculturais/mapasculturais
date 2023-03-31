app.component('opportunity-phase-config-results' , {
    template: $TEMPLATES['opportunity-phase-config-results'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-results');
        return { text };
    },

    props: {
        phases: {
            type: Array,
            required: true
        },
        phase: {
            type: Entity,
            required: true
        }
    },

    computed: {
        index() {
            return this.phases.indexOf(this.phase);
        },

        previousPhase() {
            return this.phases[this.index - 1];
        },

        previousPhaseDateTo() {
            const previousPhase = this.previousPhase;
            return previousPhase.registrationTo || previousPhase.evaluationTo;
        },

        minDate() {
            return this.previousPhaseDateTo;
        },

        isPublishLocked() {
            // const previousPhaseDateTo = this.previousPhaseDateTo
            // return previousPhaseDateTo ? this.previousPhaseDateTo.isFuture() : true;

            // const date = this.phase.evaluationMethodConfiguration?.evaluationTo || this.phase.registrationTo;

            const phase = this.phases[this.index + 1];

            if(phase.__objectType === 'evaluationmethodconfiguration') {
                return phase.evaluationTo.isFuture();
            } else {
                return phase.registrationTo.isFuture();
            }

            return false;
        }
    },

    methods: {
    }
});