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
        }, 
        hideTitle: {
            type: Boolean,
            default: false
        },
        useSealsCertification: {
            type: Boolean,
            default: false
        },
    },

    data() {
        return {
            now: new Date(),
            publishTimestampFieldKey: 0,
            publishTimestampPastError: false,
        };
    },

    mounted() {
        if (!this.hideCheckbox || !this.hideDatepicker) {
            this.nowInterval = setInterval(() => {
                this.now = new Date();
            }, 10000);
        }
    },

    beforeUnmount() {
        clearInterval(this.nowInterval);
    },

    computed: {

        exampleNow() {
            return new McDate(this.now);
        },

        exampleTimeNow() {
            return this.exampleNow.time();
        },

        exampleDateNow() {
            return this.exampleNow.date('2-digit year');
        },

        exampleTimePlusOne() {
            const date = new McDate(new Date(this.now.getTime() + 60000));
            const time = date.time();

            // na virada do dia, mostra também a data para o exemplo não apontar para um horário de hoje
            if (date.date('2-digit year') !== this.exampleDateNow) {
                return `${time} ${this.text('de')} ${date.date('2-digit year')}`;
            }

            return time;
        },

        suggestedPublishDate() {
            const date = new Date(this.now.getTime() + 2 * 60000);
            date.setSeconds(0, 0);
            return date;
        },

        suggestedPublishTime() {
            return new McDate(this.suggestedPublishDate).time();
        },

        isPublishTimestampPast() {
            const date = this.phase.publishTimestamp?._date;
            return date instanceof Date && date < this.now;
        },

        index() {
            let index = this.phases.indexOf(this.phase);

            if(index == -1) {
                index = this.phases.indexOf(this.phase.evaluationMethodConfiguration);
            }

            return index;
        },

        isNotContinuousFlow () {
            return !this.firstPhase.isContinuousFlow;
        },

        previousPhase() {
            return this.phases[this.index - 1];
        },

        nextPhase() {
            return this.phases[this.index + 1];
        },

        minDate () { 
            if (this.phase.isAppealPhase) {
                return this.phase.evaluationMethodConfiguration.evaluationTo?._date || this.phase.evaluationMethodConfiguration.registrationTo?._date;
            } else {
                let phase;
                if(this.phase.isLastPhase) {
                    phase = this.previousPhase;
                } else {
                    phase = this.phase;
                }
                const result = phase.evaluationTo?._date || phase.registrationTo?._date;      
                return result;
            }
        },
        maxDate () {
            if (this.phase.isAppealPhase) {
                return null;
            } else {
                if(this.phase.isLastPhase) {
                    return null;
                } else if(this.nextPhase.isLastPhase) {
                    return this.nextPhase.publishTimestamp?._date;
                } else {
                    return this.nextPhase.evaluationTo?._date || this.nextPhase.registrationTo?._date;
                }
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
        // Impede salvar data de publicação no passado: devolve o último valor salvo,
        // e o autosave do entity-field não encontra alteração para enviar.
        onPublishTimestampChange() {
            const date = this.phase.publishTimestamp?._date;

            if (!(date instanceof Date) || date >= new Date()) {
                this.publishTimestampPastError = false;
                return;
            }

            // o datepicker continua mostrando o que foi digitado, para a pessoa só corrigir a hora
            const original = this.phase.__originalValues?.publishTimestamp;
            this.phase.publishTimestamp = original ? new McDate(original) : null;
            this.publishTimestampPastError = true;
        },

        useSuggestedPublishDate() {
            this.phase.publishTimestamp = new McDate(this.suggestedPublishDate);
            this.publishTimestampPastError = false;
            // recria o datepicker, que não relê o valor da entidade sozinho
            this.publishTimestampFieldKey++;
            this.phase.save();
        },

        publishRegistration () {
            this.phase.POST('publishRegistrations', this.phase).then(item => {
                this.phase.publishedRegistrations = item.publishedRegistrations
            });
        },
        unpublishRegistration () {
            this.phase.POST('unpublishRegistrations', this.phase).then(item => {
                this.phase.publishedRegistrations = item.publishedRegistrations
            });
        }
    }
});