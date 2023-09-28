app.component('opportunity-phase-publish-date-config' , {
    template: $TEMPLATES['opportunity-phase-publish-date-config'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-publish-date-config');
        return { text };
    },

    props: {
        phase: {
            type: Entity,
            required: true
        },
        phases: {
            type: Array,
            required: true
        },
        hideButton: {
            type: Boolean,
            default: false
        },
        hideDatepicker: {
            type: Boolean,
            default: false
        },
        hideCheckbox: {
            type: Boolean,
            default: false
        },
        hideDescription: {
            type: Boolean,
            default: false
        }
    },

    computed: {

        index() {
            let index = this.phases.indexOf(this.phase);

            if(index == -1) {
                index = this.phases.indexOf(this.phase.evaluationMethodConfiguration);
            }

            return index;
        },

        previousPhase() {
            return this.phases[this.index - 1];
        },

        nextPhase() {
            return this.phases[this.index + 1];
        },

        minDate () { 
            let phase;
            if(this.phase.isLastPhase) {
                phase = this.previousPhase;
            } else {
                phase = this.phase;
            }
            const result = phase.evaluationTo?._date || phase.registrationTo?._date;      
            return result;
        },
        maxDate () {
            if(this.phase.isLastPhase) {
                return null;
            } else if(this.nextPhase.isLastPhase) {
                return this.nextPhase.publishTimestamp?._date;
            } else {
                return this.nextPhase.evaluationTo?._date || this.nextPhase.registrationTo?._date;
            }
        },
        firstPhase() {
            const firstPhase = this.phases[0];
            if (firstPhase.isFirstPhase) {
                return firstPhase;
            }
        },
        lastPhase() {
            const lastPhase = this.phases[this.phases.length - 1];
            if (lastPhase.isLastPhase) {
                return lastPhase;
            }
        },
        isPublished() {
            return this.firstPhase.status > 0;
        },
    },

    methods: {
        publishRegistration () {
            this.phase.POST('publishRegistrations', this.phase).then(item => {
                this.phase.populate(item);
            });
        },
        unpublishRegistration () {
            this.phase.POST('unpublishRegistrations', this.phase).then(item => {
                this.phase.populate(item);
            });
        }
    }
});