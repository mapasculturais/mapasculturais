app.component('opportunity-counter-argument-phase-config' , {
    template: $TEMPLATES['opportunity-counter-argument-phase-config'],

    setup() {
        const text = Utils.getTexts('opportunity-counter-argument-phase-config');
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

        tab: {
            type: String,
        },
    },

    data() {
        return {
            processing: false,
            entity: null,
        }
    },

    mounted() {
        this.initializeCounterArgumentPhase();
    },

    computed: {
        target() {
            return this.phase.__objectType === 'evaluationmethodconfiguration'
                ? this.phase.opportunity
                : this.phase;
        },

        firstPhase() {
            return this.phases[0];
        },

        isTechnicalEvaluationPhase() {
            return this.phase?.type?.id === 'technical' ||
                this.phase?.type === 'technical' ||
                this.target?.evaluationMethodConfiguration?.type?.id === 'technical' ||
                this.target?.evaluationMethodConfiguration?.type === 'technical';
        },

        canConfigure() {
            return this.isTechnicalEvaluationPhase &&
                !!(this.target?.appealPhase || this.phase?.appealPhase);
        },

        fromDateMin() {
            return this.target.appealPhase?.registrationTo ||
                this.target.publishTimestamp ||
                this.target.evaluationMethodConfiguration?.evaluationFrom ||
                this.phase?.evaluationFrom;
        },

        toDateMin() {
            return this.entity?.registrationFrom || this.entity?.evaluationMethodConfiguration?.evaluationFrom;
        },
    },

    methods: {
        async createCounterArgumentPhase() {
            this.processing = true;
            const messages = useMessages();

            await this.target.POST('createCounterArgumentPhase', {})
                .then((data) => {
                    this.entity = Entity.fromJson(data);
                    this.target.counterArgumentPhase = this.entity;
                    this.processing = false;

                    messages.success(this.text('Fase de contrarrazão criada com sucesso'));
                })
                .catch((error) => {
                    messages.error(error.data);
                    this.processing = false;
                });
        },

        initializeCounterArgumentPhase() {
            const counterArgumentPhaseRef = this.target?.counterArgumentPhase;
            if (!counterArgumentPhaseRef?.id) {
                this.entity = null;
                return;
            }

            if (counterArgumentPhaseRef instanceof Entity) {
                this.entity = counterArgumentPhaseRef;
            } else {
                this.entity = Entity.fromJson({ '@entityType': 'opportunity', ...counterArgumentPhaseRef });
            }

            this.target.counterArgumentPhase = this.entity;
        },

        async deleteCounterArgumentPhase() {
            const messages = useMessages();
            this.processing = true;
            const entity = this.entity;

            try {
                this.entity = null;
                await entity.destroy();
                this.target.counterArgumentPhase = null;
                messages.success(this.text('Fase de contrarrazão excluída com sucesso'));
            } catch (error) {
                this.entity = entity;
                this.target.counterArgumentPhase = entity;
                messages.error(error);
            }

            this.processing = false;
        }
    }
});
