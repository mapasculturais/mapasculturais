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
        maxDate () {
            return this.opportunity.evaluationMethodConfiguration.evaluationTo?._date ?? null; 
        },

        minDate () {
            return this.opportunity.evaluationMethodConfiguration.evaluationFrom?._date ?? null;
        },
    },

    mounted () {
        console.log(this.opportunity);
    },

    methods: {
        createEntities () {
            this.collectionPhase = Vue.reactive(new Entity('evaluationmethodconfiguration'));
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

            // modal.loading(true);
            try {
                api.POST(url, data).then((res) => res.json()).then((data) => {
                    console.log(data);
                });
                // this.$emit
                // modal.close();
                // modal.loading(false);
            } catch (err) {
                console.error(err);
                modal.loading(false);
            }
        },
    }
})