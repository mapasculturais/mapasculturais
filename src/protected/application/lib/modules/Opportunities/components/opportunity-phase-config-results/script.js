app.component('opportunity-phase-config-results' , {
    template: $TEMPLATES['opportunity-phase-config-results'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-results');
        return { text };
    },

    props: {
        currentIndex: {
            type: Number,
            required: true
        },
        entity: {
            type: Entity,
            required: true
        },
        phases: {
            type: Array,
            required: true
        }
    },

    methods: {
        addPublishRegistrations (phase) {
            phase.POST('publishRegistrations');
        },
        isBlockedPublish () {
            const previousPhase = this.phases[this.currentIndex - 1];
            const dtFinal = previousPhase.evaluationTo?._date || null;
            return dtFinal > new Date();
        },
        getMinDate () {
            const previousPhase = this.phases[this.currentIndex - 1];
            const currentPhase = this.phases[this.currentIndex];

            if (currentPhase.__objectType === 'opportunity') {
                return previousPhase.registrationTo?._date || previousPhase.evaluationTo?._date;
            } else if (previousPhase && currentPhase.__objectType === 'evaluationmethodconfiguration') {
                if(previousPhase.__objectType === 'evaluationmethodconfiguration') {
                    return previousPhase.registrationTo?._date || null;
                } else if(previousPhase.__objectType === 'opportunity') {
                    return previousPhase.registrationFrom?._date || null;
                }
            }
        },
        getMaxDate () {
            const currentPhase = this.phases[this.currentIndex];
            return currentPhase.publishTimestamp?._date || null;
        }
    }
});