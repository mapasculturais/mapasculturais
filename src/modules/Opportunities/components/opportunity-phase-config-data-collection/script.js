app.component('opportunity-phase-config-data-collection' , {
    template: $TEMPLATES['opportunity-phase-config-data-collection'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-data-collection');
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
        }
    },

    computed: {
        index() {
            return this.phases.indexOf(this.phase);
        },

        previousPhase() {
            return this.phases[this.index - 1];
        },

        nextPhase() {
            return this.phases[this.index + 1];
        },

        fromDateMin() {
            if(this.phase.isFirstPhase) {
                return null;
            }
            return this.previousPhase.evaluationFrom || this.previousPhase.registrationFrom;
        },

        fromDateMax() {
            let date;

            // se a data final da fase está definida, a data inicial não pode ser maior que a data final
            if (this.phase.registrationTo) {
                date = this.phase.registrationTo;

            // senão, se a próxima fase é a última fase, a data inicial não pode ser maior que a data de publicação final dos resultados
            } else if(this.nextPhase.isLastPhase) {
                date = this.nextPhase.publishTimestamp;
            
            // senão, a data inicial não pode ser maior que a data final da próxima fase
            } else {
                date = this.nextPhase.registrationTo || this.nextPhase.evaluationTo;
            }

            return date;
        },

        toDateMin(){
            let date;

            // se a data inicial da fase está definida, a data final não pode ser menor que a data inicial
            if (this.phase.registraionFrom) {
                date = this.phase.registraionFrom;

            // senão, a data inicial não pode ser enor que a data inicial da fase anterior
            } else if(this.previousPhase) {
                date = this.previousPhase.registrationfrom || this.previousPhase.evaluationfrom;
            }

            return date;
        },

        toDateMax() {
            let date;
            
            if (this.nextPhase.isLastPhase) {
                date = this.nextPhase.publishTimestamp;
            } else {
                date = this.nextPhase.registrationTo || this.nextPhase.evaluationTo;
            }

            return date;
        },

        firstPhase() {
            return this.phases[0];
        },

        confirmDeleteMessage () {
            if (this.phase?.isReportingPhase) {
                return this.text('confirma exclusao de fase de prestacao');
            } else {
                return this.text('confirma exclusao de fase');
            }
        },
    },

    methods: {
        removeEvaluationPhase (item) {
            if (item.evaluationMethodConfiguration?.id) {
                const evaluationId = item.evaluationMethodConfiguration.id;
                const evaluationIndex = this.phases.findIndex((phase) => {
                    return phase.__objectType === 'evaluationmethodconfiguration' && phase.id == evaluationId;
                });
                if (evaluationIndex >= 0) {
                    this.phases.splice(evaluationIndex, 1);
                }
            }
        },
        async deletePhase (item, index) {
            const messages = useMessages();
            try{
                await item.destroy();
                this.phases.splice(index, 1);
                if (this.phase?.isReportingPhase) {
                    this.removeEvaluationPhase(item);
                }
            } catch (e) {
                messages.error(this.text('nao foi possivel remover fase'));
            }
        },
    }
});