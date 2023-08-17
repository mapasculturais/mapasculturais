app.component('entity-field-datepicker', {
    template: $TEMPLATES['entity-field-datepicker'],
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
            type: [ String, Date ],
            default: null
        },
        maxDate: {
            type: [ String, Date ],
            default: null
        },
        propId: {
            type: String,
            default: ''
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
            dayNames: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
            dateTextInputOptions: {'format': 'dd/MM/yyyy'},
            datetimeTextInputOptions: {'format': 'dd/MM/yyyy HH:mm'},
            timeTextInputOptions: {'format': 'HH:mm'},
        };
    },

    computed: {
        hasErrors() {
            let errors = this.entity.__validationErrors[this.prop] || [];
            if(errors.length > 0){
                return true;
            } else {
                return false;
            }
        },
        errors() {
            return this.entity.__validationErrors[this.prop];
        },
        value() {
            return this.entity[this.prop]?.id ?? this.entity[this.prop];
        },
    },

    methods: {
        is(val) {
            return val === this.fieldType;
        },
        onChange(event, onInput) {
            if(event instanceof InputEvent) {
                setTimeout(() => onInput(event), 50);
            }
        },
        dateFormat(date) {
            let mcdate = new McDate (date);
            return mcdate.date('2-digit year');
        },

        datetimeFormat(date) {
            let mcdate = new McDate (date);
            return mcdate.date('2-digit year') + ' ' + mcdate.time('2-digit');
        },

        change(val) {
            this.entity.__validationErrors[this.prop] = [];
            if (this.maxDate && val > this.maxDate) {
                this.entity.__validationErrors[this.prop].push("Data acima do permitido");
            }
            if (this.minDate && val < this.minDate) {
                this.entity.__validationErrors[this.prop].push("Data abaixo do permitido");
            }            
            this.$emit('change', val);
        }
    }
});