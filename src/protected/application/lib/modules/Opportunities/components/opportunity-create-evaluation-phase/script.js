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
            return this.lastPhase.publishTimestamp._date || null;
        },
        minDate () {
            return this.previousPhase.registrationTo?._date || this.previousPhase.evaluationTo?._date;
        },
        minDateEvaluationTo () {
            return this.phase.evaluationFrom?._date || '';
        }
    },

    methods: {

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