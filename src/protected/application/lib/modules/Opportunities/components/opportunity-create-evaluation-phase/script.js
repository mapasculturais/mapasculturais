app.component('opportunity-create-evaluation-phase' , {
    template: $TEMPLATES['opportunity-create-evaluation-phase'],

    data () {
        return {
            phase: null
        };
    },

    props: {
        opportunity: {
            type: Entity,
            required: true
        }
    },

    mounted() {
    },

    methods: {
        dateFormat(date) {
            return new Date(date).toLocaleString();
        },
        createEntity() {
            this.phase = Vue.ref(new Entity('evaluationmethodconfiguration'));

            this.phase.type = this.opportunity.type;
            this.phase.status = -1;
            this.phase.parent = this.opportunity;
            this.phase.registrationFrom = this.opportunity.registrationFrom;
            this.phase.registrationTo = this.opportunity.registrationTo;

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