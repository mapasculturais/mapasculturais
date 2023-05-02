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
        isBlockPublish () {
            const date = this.phase.evaluationMethodConfiguration?.evaluationTo || this.phase.registrationTo;
            return !!date ? date.isFuture() : false;
        },
        msgAutoPublish () {
            return this.text('publicacao_automatica');
        },
        msgPublishDate () {
            return this.text('publicacao_com_data') + ' ' + this.phase.publishTimestamp?.format({ dateStyle: 'full', timeStyle: 'long'});
        },
        msgPublishDateAuto () {
            return this.text('publicacao_com_data_automatica') + ' ' + this.phase.publishTimestamp?.format({ dateStyle: 'full', timeStyle: 'long'});
        },
        minDate () {
            return this.phase.evaluationTo?._date || this.phase.registrationTo?._date;
        },
        maxDate () {
            if(!this.phase.isLastPhase) {
                return this.lastPhase?.publishTimestamp?._date;
            }
        },

        lastPhase() {
            const lastPhase = this.phases[this.phases.length - 1];
            if (lastPhase.isLastPhase) {
                return lastPhase;
            }
        }
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