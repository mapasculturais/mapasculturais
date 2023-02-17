app.component('opportunity-create-evaluation-phase' , {
    template: $TEMPLATES['opportunity-create-evaluation-phase'],
    emits: ['create'],

    data () {
        return {
            phase: null,
            dateStart: '',
            dateEnd: ''
        };
    },

    props: {
        opportunity: {
            type: Entity,
            required: true
        },
        previousPhase: {
            type: Entity,
            required: true
        },
        lastPhase: {
            type:Entity,
            required: true
        }
    },

    computed: {
        maxDate () {
            return this.dateEnd;
        },
        minDate () {
            return this.dateStart;
        },
        minDateEvaluationTo () {
            return this.phase.evaluationFrom?._date || '';
        }
    },

    mounted () {
        this.dateStart = $MAPAS.requestedEntity.registrationFrom.date;
        this.dateEnd = $MAPAS.requestedEntity.registrationTo.date;
    },

    methods: {
        dateFormat(date) {
            return new Date(date).toLocaleString();
        },
        createEntity() {
            this.phase = Vue.ref(new Entity('evaluationmethodconfiguration'));
            this.phase.opportunity = this.opportunity;
        },
        destroyEntity() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => this.entity = null, 200);
        },
        save(modal) {
            modal.loading(true);
            this.phase.save().then((response) => {
                this.$emit('create', response);
                modal.loading(false);
                modal.close();
            }).catch((e) => {
                modal.loading(false);
            });
        },
    }
});