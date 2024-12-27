app.component('opportunity-appeal-phase-config' , {
    template: $TEMPLATES['opportunity-appeal-phase-config'],

    setup() {
        const text = Utils.getTexts('opportunity-appeal-phase-config');
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
    },

    data() {
        return {
            processing: false,
            phaseData: {},
            entity: null,
            moreResponse: false,
            showButtonEvaluationCommittee: true,   
        }
    },

    mounted() {
        this.initializeAppealPhase();
    },

    computed: {
        firstPhase() {
            return this.phases[0];
        },

        lastPhase() {
            const lastPhase = this.phases[this.phases.length - 1];
            if (lastPhase.isLastPhase) {
                return lastPhase;
            }
        },

        fromDateMin() {
            return this.phase.publishTimestamp || this.phase.registrationFrom || this.phase.evaluationMethodConfiguration?.evaluationFrom;
        },

        fromDateMax() {
            return null;
        },

        toDateMin() {
            return this.phase.appealPhase?.registrationFrom || this.phase.appealPhase?.evaluationMethodConfiguration?.evaluationFrom;
        },

        toDateMax() {
            return null;
        },

        registrationFrom() {
            return this.entity.registrationFrom
                ? this.entity.registrationFrom.format({ day: '2-digit', month: '2-digit', year: 'numeric' })
                : '';
        },

        registrationTo() {
            return this.entity.registrationTo
                ? this.entity.registrationTo.format({ day: '2-digit', month: '2-digit', year: 'numeric' })
                : '';
        },

        evaluationFrom() {
            return this.entity.evaluationMethodConfiguration.evaluationFrom 
                ? this.entity.evaluationMethodConfiguration.evaluationFrom.format({ day: '2-digit', month: '2-digit', year: 'numeric' })
                : '';
        },

        evaluationTo() {
            return this.entity.evaluationMethodConfiguration.evaluationTo
                ? this.entity.evaluationMethodConfiguration.evaluationTo.format({ day: '2-digit', month: '2-digit', year: 'numeric' })
                : '';
        },

    },

    methods: {
        async createAppealPhase() {
            this.processing = true;
            const messages = useMessages();
        
            const target = this.phase.__objectType === 'evaluationmethodconfiguration' 
                ? this.phase.opportunity 
                : this.phase;
        
            let args = {};
        
            await target.POST('createAppealPhase', args)
                .then((data) => {
                    this.phaseData = data;
        
                    this.entity = new Entity('opportunity');
                    this.entity.populate(this.phaseData);
                    this.processing = false;
        
                    messages.success(this.text('Fase de recurso criada com sucesso'));
                })
                .catch((data) => {
                    messages.error(data.error);
                    this.processing = false;
                });
        },

        initializeAppealPhase() {
            this.entity = this.phase.appealPhase;
        },

        addEvaluationCommittee() {
            this.showButtonEvaluationCommittee = false;
        },

        async deleteAppealPhase() {
            const messages = useMessages();
            this.processing = true;
            const entity = this.entity;

            try {
                this.entity = null;
                await entity.destroy();
                messages.success(this.text('Fase de recurso excluída com sucesso'));
                
            } catch (error) {
                this.entity = entity;
                messages.error(error);
            }

            this.processing = false;         
        }

    }
});