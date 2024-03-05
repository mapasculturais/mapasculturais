app.component('opportunity-phase-publish-config-registration' , {
    template: $TEMPLATES['opportunity-phase-publish-config-registration'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-publish-config-registration');
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
        },
        tab: {
            type: String,
        },
    },

    computed: {
        isOpenPhase() {
            if(this.phase?.evaluationMethodConfiguration) {
                return this.phase?.evaluationMethodConfiguration?.evaluationTo.isFuture();
            }

            return this.phase?.registrationTo?.isFuture();

        },
        minDate () {
            return this.phase.evaluationTo?._date || this.phase.registrationTo?._date;
        },
        maxDate () {
            if(!this.phase.isLastPhase) {
                return this.lastPhase?.publishTimestamp?._date;
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