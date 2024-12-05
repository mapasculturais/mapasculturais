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
            handler(value) {
                if (value) {
                    this.entity[this.prop] = new McDate(value);
                }
            }
        },

        modelDate: {
            handler(value) {
                if (value) {
                    this.dateInput = this.modelDate ? new McDate(this.modelDate).date('2-digit year') : '';
                    this.updateDateTime();
                }
            }
        },

        modelTime: {
            handler(value) {
                if (value) {
                    this.timeInput = this.modelTime ? `${this.modelTime.hours.toString().padStart(2, '0')}:${this.modelTime.minutes.toString().padStart(2, '0')}` : '';
                    this.updateDateTime();
                }
            }
        },

        dateInput(value) {
            if (!this.isDateInputFocused && value && value.length === 10) {
                this.inputValue('date');
            }
        },

        timeInput(value) {
            if (!this.isTimeInputFocused && value && value.length === 5) {
                this.inputValue('time');
            }
        }
    },

    mounted () {
        this.model = this.entity[this.prop]?._date;
        this.modelDate = this.entity[this.prop]?._date;
        if (this.entity[this.prop]?.time('full')) {
            let time = this.entity[this.prop]?.time('full').split(':');
            this.modelTime = {
                hours: time[0],
                minutes: time[1],
                seconds: 0
            };
        } else {
            this.modelTime = '';
        }

        this.timeInput = this.entity[this.prop]?.time('full');
        this.dateInput = this.entity[this.prop]?.date('2-digit year');
        
    },

    data () {
        return {
            model: '',
            modelDate: '',
            modelTime: '',
            timeInput: '',
            dateInput: '',
            isDateInputFocused: false,
            isTimeInputFocused: false,
            locale: $MAPAS.config.locale,
            dayNames: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
            dateTextInputOptions: {'format': 'dd/MM/yyyy'},
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

        dateFormat() {
            let mcdate = this.entity[this.prop];
            if (mcdate == null) {
                return '';
            }
            return mcdate.date('2-digit year');
        },

        timeFormat() {
            let mcdate = this.entity[this.prop];
            return mcdate?.time('full');
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
        },

        onDateChange(value) {
            this.modelDate = value;
            this.updateDateTime();
            this.change(this.modelDate);
        },

        onTimeChange(value) {
            this.modelTime = value;
            this.updateDateTime();
        },

        onDateInput() {
            if (this.modelDate && this.modelTime) {
                let datetime = new McDate(this.modelDate)._date;
                
                datetime.setHours(this.modelTime.hours);
                datetime.setMinutes(this.modelTime.minutes);
        
                this.change(datetime);
                this.$emit('change', datetime);
            }
        },

        handleBlur(type) {
            if (type === 'date' && this.dateInput?.length === 10) {
                const [day, month, year] = this.dateInput.split('/');
                if (day && month && year) {
                    this.modelDate = new McDate(`${year}-${month}-${day}`)._date;
                    
                    // Verifica se modelTime está definido, se não, define com a hora atual
                    if (!this.modelTime) {
                        this.modelTime = {
                            hours: new Date().getHours(),
                            minutes: new Date().getMinutes(),
                            seconds: 0
                        };
                    }
        
                    this.updateDateTime();
                }
            } else if (type === 'time' && this.timeInput?.length === 5) {
                const [hours, minutes] = this.timeInput.split(':');
                if (hours && minutes) {
                    this.modelTime = {
                        hours: parseInt(hours, 10),
                        minutes: parseInt(minutes, 10),
                        seconds: 0
                    };
                    this.updateDateTime();
                }
            }
        },

        inputValue(type) {
            if (type === 'date' && this.dateInput.length === 10) {
                const [day, month, year] = this.dateInput.split('/');
                this.modelDate = new McDate(`${year}-${month}-${day}`)._date;
            } else if (type === 'time' && this.timeInput.length === 5) {
                const [hours, minutes] = this.timeInput.split(':');
                this.modelTime = {
                    hours: parseInt(hours, 10),
                    minutes: parseInt(minutes, 10),
                    seconds: 0
                };
            }
            this.updateDateTime();
        },

        updateDateTime() {
            if (this.modelDate && this.modelTime) {
                let datetime = new McDate(this.modelDate)._date;
                datetime.setHours(this.modelTime.hours);
                datetime.setMinutes(this.modelTime.minutes);
                this.change(datetime); 
                this.$emit('change', datetime);
            }
        },
    }
});