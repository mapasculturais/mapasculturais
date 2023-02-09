app.component('mc-input-datepicker-wrapper', {
    template: $TEMPLATES['mc-input-datepicker-wrapper'],
    emits: ['change'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        prop: {
            type: String,
            required: true
        },

        fieldType: {
            type: String,
            required: true
        },
        minDate: {
            type: String,
            default: null
        },
        maxDate: {
            type: String,
            default: null
        }
    },

    watch: {
      model: {
          handler (value) {
              if(value) {
                  this.entity[this.prop] = new McDate(value);
              }
          }
      }
    },

    mounted () {
        this.model = this.entity[this.prop]?._date;
    },

    data () {
        return {
            model: '',
            locale: $MAPAS.config.locale,
            dayNames: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab']
        };
    },

    computed: {
        isMinDate () {
            return this.minDate !== null || this.maxDate !== null;
        }
    },

    methods: {
        is(val) {
            return val === this.fieldType;
        },
        dateFormat(date) {
            return new Date(date).toLocaleString();
        }
    }
});
