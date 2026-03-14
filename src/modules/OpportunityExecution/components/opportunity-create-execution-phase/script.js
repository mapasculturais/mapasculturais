app.component('opportunity-create-execution-phase', {
    template: $TEMPLATES['opportunity-create-execution-phase'],
    emits: ['create'],

    props: {
        opportunity: {
            type: Entity,
            required: true,
        },
    },

    data() {
        return {
            collectionPhase: null,
            evaluationPhase: null,
        };
    },

    methods: {
        createEntities() {
            this.collectionPhase = Vue.reactive(new Entity('opportunity'));
            this.evaluationPhase = Vue.reactive(new Entity('evaluationmethodconfiguration'));
        },

        destroyEntities() {
            setTimeout(() => {
                this.collectionPhase = null;
                this.evaluationPhase = null;
            }, 200);
        },

        async save(modal) {
            modal.loading(true);
            try {
                const data = await this.opportunity.invoke('createExecutionPhase', {
                    collectionPhase: this.collectionPhase,
                    evaluationPhase: this.evaluationPhase,
                });

                const collectionPhase = Vue.reactive(new Entity('opportunity'));
                collectionPhase.populate(data.collectionPhase);
                const evaluationPhase = Vue.reactive(new Entity('evaluationmethodconfiguration'));
                evaluationPhase.populate(data.evaluationPhase);

                this.$emit('create', { collectionPhase, evaluationPhase });
                modal.close();
            } catch (err) {
                if (err.collectionErrors) {
                    this.collectionPhase.__validationErrors = err.collectionErrors;
                }
                if (err.evaluationErrors) {
                    this.evaluationPhase.__validationErrors = err.evaluationErrors;
                }
            }
            modal.loading(false);
        },
    },
});
