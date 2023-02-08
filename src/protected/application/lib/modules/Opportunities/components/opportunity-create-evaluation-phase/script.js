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
              $MAPAS.requestedEntity.registrationFrom.date = val;
          }
      },
      dateEnd: {
          handler (val) {
              $MAPAS.requestedEntity.registrationTo.date = val;
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

        },
        destroyEntity() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => this.entity = null, 200);
        },
    }
});