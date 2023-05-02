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
            const currentIndex = this.phases.indexOf(this.phase);

            if(currentIndex === 0) {
                return undefined;
            }

            const previousPhase = this.phases[currentIndex - 1];

            if (previousPhase.__objectType == 'evaluationmethodconfiguration') {
                // fase anterior é uma fase de avaliação
                return previousPhase.evaluationTo?._date;
            } else {
                // fase anterior é uma fase de coleta de dados
                return previousPhase.registrationFrom?._date;
            }
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