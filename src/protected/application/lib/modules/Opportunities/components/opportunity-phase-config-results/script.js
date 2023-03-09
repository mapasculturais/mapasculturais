app.component('opportunity-phase-config-results' , {
    template: $TEMPLATES['opportunity-phase-config-results'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-results');
        return { text };
    },

    data () {
        return {};
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

    computed: {
    },

    mounted () {
    },

    methods: {
        addPublishRegistrations (phase) {
            phase.POST('publishRegistrations');
        },
        isBlockedPublish (index) {
            const previousPhase = this.getPreviousPhase(index);
            const dtFinal = previousPhase.evaluationTo?._date || null;
            return dtFinal > new Date();
        },
        getMinDate (phase, index) {
            const previousPhase = this.getPreviousPhase(index);

            if (phase === 'opportunity') {
                return previousPhase.registrationTo?._date || previousPhase.evaluationTo?._date;
            } else if (phase === 'evaluationmethodconfiguration') {
                if(previousPhase.__objectType === 'evaluationmethodconfiguration') {
                    return previousPhase.registrationTo?._date;
                } else if(previousPhase.__objectType === 'opportunity') {
                    return previousPhase.registrationFrom?._date;
                }
            }
        },
        getMaxDate (phase, index) {
            if(phase.isLastPhase) {
                return phase.publishTimestamp?._date;
            }
        },
        getPreviousPhase (currentIndex) {
            if(this.phases[currentIndex - 1]) {
                return this.phases[currentIndex - 1];
            } else {
                return undefined;
            }
        },
        getNextPhase (currentIndex) {
            if(this.phases[currentIndex + 1]) {
                return this.phases[currentIndex + 1];
            } else {
                return undefined;
            }
        },
    }
});