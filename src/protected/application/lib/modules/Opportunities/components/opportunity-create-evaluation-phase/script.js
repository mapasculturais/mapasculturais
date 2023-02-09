app.component('opportunity-create-evaluation-phase' , {
    template: $TEMPLATES['opportunity-create-evaluation-phase'],

    data () {
        return {
            phase:null,
            locale: $MAPAS.config.locale,
            dateStart: '',
            dateEnd: '',
            dateFinalResult: '',
            dayNames: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
            accountability: false
        };
    },

    props: {
        opportunity: {
            type: Entity,
            required: true
        }
    },

    mounted() {
        this.dateStart = $MAPAS.requestedEntity.registrationFrom.date;
        this.dateEnd = $MAPAS.requestedEntity.registrationTo.date;
    },

    watch: {
      dateStart: {
          handler (val) {
              $MAPAS.requestedEntity.evaluationTo = val;
          }
      },
      dateEnd: {
          handler (val) {
              $MAPAS.requestedEntity.evaluationTo = val;
          }
      }
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