app.component('opportunity-create-reporting-phase', {
    template: $TEMPLATES['opportunity-create-reporting-phase'],
    emits: ['create'],

    props: {
        isFinal: {
            type: Boolean,
            default: false,
        },
        opportunity: {
            type: Entity,
            required: true,
        },
    },

    data () {
        return {
            collectionPhase: null,
            evaluationPhase: null,
        }
    },

    computed: {
        minCollectionDate () {
            return this.opportunity.evaluationMethodConfiguration.evaluationTo?._date ?? null;
        },
    },

    methods: {
        createEntities () {
            this.collectionPhase = Vue.reactive(new Entity('opportunity'));
            this.collectionPhase.isFinalReportingPhase = this.isFinal;
            this.evaluationPhase = Vue.reactive(new Entity('evaluationmethodconfiguration'));
        },

        destroyEntities () {
            // para o conteúdo da modal não sumir antes de fechar
            setTimeout(() => {
                this.collectionPhase = null;
                this.evaluationPhase = null;
            }, 200);
        },

        async save (modal) {
            const api = new API();
            const url = Utils.createUrl('projectReporting', 'reportingPhase');
            const data = {
                collectionPhase: this.collectionPhase,
                evaluationPhase: this.evaluationPhase,
                parent: this.opportunity.id,
            };

            modal.loading(true);
            try {
                const res = await api.POST(url, data);
                if (res.ok) {
                    const data = await res.json();

                    const collectionPhase = Vue.reactive(new Entity('opportunity'));
                    collectionPhase.populate(data.collectionPhase);
                    const evaluationPhase = Vue.reactive(new Entity('evaluationmethodconfiguration'));
                    evaluationPhase.populate(data.evaluationPhase);

                    this.$emit('create', { collectionPhase, evaluationPhase });
                    modal.close();
                    modal.loading(false);
                } else {
                    const { collectionErrors, evaluationErrors } = await res.json()
                    this.collectionPhase.__validationErrors = collectionErrors ?? [];
                    this.evaluationPhase.__validationErrors = evaluationErrors ?? [];
                    modal.loading(false);
                }
            } catch (err) {
                console.error(err);
                modal.loading(false);
            }
        },
    }
})