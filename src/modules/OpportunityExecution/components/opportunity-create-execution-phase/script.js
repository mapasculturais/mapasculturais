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
            const api = new API('opportunity');
            const url = this.opportunity.getUrl('createExecutionPhase');
            const data = {
                collectionPhase: this.collectionPhase,
                evaluationPhase: this.evaluationPhase,
            };

            modal.loading(true);
            try {
                const res = await api.POST(url, data);
                if (res.ok) {
                    const responseData = await res.json();

                    const collectionPhase = Vue.reactive(new Entity('opportunity'));
                    collectionPhase.populate(responseData.collectionPhase);
                    const evaluationPhase = Vue.reactive(new Entity('evaluationmethodconfiguration'));
                    evaluationPhase.populate(responseData.evaluationPhase);

                    this.$emit('create', { collectionPhase, evaluationPhase });
                    modal.close();
                    modal.loading(false);
                } else {
                    const { collectionErrors, evaluationErrors } = await res.json();
                    this.collectionPhase.__validationErrors = collectionErrors ?? [];
                    this.evaluationPhase.__validationErrors = evaluationErrors ?? [];
                    modal.loading(false);
                }
            } catch (err) {
                console.error(err);
                modal.loading(false);
            }
        },
    },
});
