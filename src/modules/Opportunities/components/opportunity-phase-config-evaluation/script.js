app.component('opportunity-phase-config-evaluation' , {
    template: $TEMPLATES['opportunity-phase-config-evaluation'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-evaluation');
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
        }, 

        tab: {
            type: String,
        },
    },

    beforeMount() {
        this.phase.infos = this.phase.infos || {general: ''};
    },

    data() {
        return {data: {}}
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

        firstPhase() {
            return this.phases[0];
        },

        fromDateMin() {
            return this.previousPhase.evaluationFrom || this.previousPhase.registrationFrom;
        },

        fromDateMax() {
            let date;

            // se a data final da fase está definida, a data inicial não pode ser maior que a data final
            if (this.phase.evaluationTo) {
                date = this.phase.evaluationTo;

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
            if (this.phase.evaluationFrom) {
                date = this.phase.evaluationFrom;

            // senão, a data inicial não pode ser enor que a data inicial da fase anterior
            } else {
                date = this.previousPhase.registrationfrom || this.previousPhase.evaluationfrom;
            }

            return date;
        },

        toDateMax() {
            let date;
            
            if (this.nextPhase?.isLastPhase) {
                date = this.nextPhase.publishTimestamp;
            } else {
                date = this.nextPhase?.registrationTo || this.nextPhase?.evaluationTo;
            }

            return date;
        },

        categories(){
            return this.phases[0].registrationCategories || [];
        },

        seals() {
            return $MAPAS.config?.opportunityPhaseConfigEvaluation?.seals;
        },

        confirmDeleteMessage () {
            if (this.phase.opportunity?.isReportingPhase) {
                return this.text('confirma exclusao de fase de prestacao');
            } else {
                return this.text('confirma exclusao de fase');
            }
        },
    },

    methods: {
        async deletePhase (phase, index) {
            const messages = useMessages();
            try {
                if (phase.opportunity?.isReportingPhase) {
                    await this.deleteReportingPhase(phase, index);
                } else {
                    await phase.delete();
                    this.phases.splice(index, 1);
                }
            } catch (e) {
                messages.error(this.text('nao foi possivel remover fase'));
            }

        },
        async deleteReportingPhase (phase, index) {
            const opportunityId = phase.opportunity.id;
            const opportunityIndex = this.phases.findIndex((phase) => {
                return phase.__objectType === 'opportunity' && phase.id == opportunityId;
            });
           
            await phase.opportunity.destroy();
            this.phases.splice(index, 1);
            this.phases.splice(opportunityIndex, 1);
        },
        savePhase () {
            this.phase.save(3000);
        }
    }
});