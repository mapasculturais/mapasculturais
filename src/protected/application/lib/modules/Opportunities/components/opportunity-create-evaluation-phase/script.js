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
            return this.lastPhase.publishTimestamp?._date || null;
        },

        minDate() {
            if (this.previousPhase.__objectType == 'evaluationmethodconfiguration') {
                // fase anterior é uma fase de avaliação
                return this.previousPhase.evaluationTo;
            } else {
                // fase anterior é uma fase de coleta de dados
                return this.previousPhase.registrationFrom;
            }
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