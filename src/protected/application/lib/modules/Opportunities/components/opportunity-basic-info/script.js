app.component('opportunity-basic-info' , {
    template: $TEMPLATES['opportunity-basic-info'],

    data () {
        return {
            locale: $MAPAS.config.locale,
            dateStart: '',
            dateEnd: '',
            dateFinalResult: '',
            dayNames: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
            accountability: false
        };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    mounted() {
        this.dateStart = $MAPAS.requestedEntity.registrationFrom?.date;
        this.dateEnd = $MAPAS.requestedEntity.registrationTo?.date;
    },

    watch: {
      dateStart: {
          handler (val) {
              if($MAPAS.requestedEntity.registrationFrom){
                $MAPAS.requestedEntity.registrationFrom.date = val;
              }
          }
      },

      dateEnd: {
          handler (val) {
            if($MAPAS.requestedEntity.registrationTo){
                $MAPAS.requestedEntity.registrationTo.date = val;
            }  
          }
      }
    },

    methods: {
        dateFormat(date) {
            return new Date(date).toLocaleString();
        }
    }
});