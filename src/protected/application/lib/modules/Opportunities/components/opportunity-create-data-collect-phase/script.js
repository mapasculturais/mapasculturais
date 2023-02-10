app.component('opportunity-create-data-collect-phase' , {
    template: $TEMPLATES['opportunity-create-data-collect-phase'],

    data () {
        return {
            phase: null
        };
    },

    props: {
        opportunity: {
            type: Entity,
            required: true
        },
        previousPhase:{
            type: Entity,
            required: true
        },
        lastPhase:{
            type:Entity,
            required: true
        }
    },

    mounted() {
        // pegar as datas do previous phase
        // se this.previousfase.__objectType == 'opportunity' then previousphase.evaluationfrom e to else registrofrom e to
    },

    watch: {
    },

    methods: {
        dateFormat(date) {
            return new Date(date).toLocaleString();
        },
        createEntity() {
            this.phase = Vue.ref(new Entity('opportunity'));
            console.log(this.phase);
            this.phase.type = this.opportunity.type;
            this.phase.status = -1;
            this.phase.parent = this.opportunity;
            this.phase.evaluationFrom = this.opportunity.evaluationFrom;
            this.phase.evaluationTo = this.opportunity.evaluationTo;
        },
        destroyEntity() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => this.entity = null, 200);
        },
        save(modal) {
            const lists = useEntitiesLists(); // obtem o storage de listas de entidades

            modal.loading(true);
            this.phase.save().then((response) => {
                this.$emit('create', response);
                modal.loading(false);
                stat = this.phase.status;
                //this.addAgent(stat);
            }).catch((e) => {
                modal.loading(false);

            });
        },
    }
});