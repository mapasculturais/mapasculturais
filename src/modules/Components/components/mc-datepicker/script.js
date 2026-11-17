app.component('mc-datepicker', {
    template: $TEMPLATES['mc-datepicker'],
    emits: ['update:modelValue'],

    props: {
        fieldType: {
            type: String,
            required: true,
            validator: (value) => ['date', 'time', 'datetime'].includes(value),
        },

        propId: {
            type: String,
            required: false,
            default: '',
        },

        locale: {
            type: String,
            // usa o idioma da instalação; 'pt-BR' só se não estiver configurado
            default: () => $MAPAS.config.locale || 'pt-BR'
        },
    },

    watch: {
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
    },

    data() {
        return {
            dateInput: '',
            timeInput: '',
            dayNames: this.getDayNames(this.locale),
            isDateInputFocused: false,
            isTimeInputFocused: false,
            dateFormat: 'dd/MM/yyyy',
            timeFormat: 'HH:mm',
            modelDate: '',
            modelTime: {
                hours: '',
                minutes: '',
                seconds: '',
            },
        }
    },

    computed: {
        isDateType() {
            return this.fieldType === 'date' || this.fieldType === 'datetime';;
        },

        isTimeType() {
            return this.fieldType === 'time' || this.fieldType === 'datetime';
        },
    },

    methods: {
        // Abreviações dos dias da semana (começando no domingo, weekStart = 0) no idioma do componente
        getDayNames(locale) {
            try {
                const formatter = new Intl.DateTimeFormat(locale, { weekday: 'short' });
                // 07/01/2024 foi um domingo
                return [0, 1, 2, 3, 4, 5, 6].map((i) => {
                    const name = formatter.format(new Date(2024, 0, 7 + i)).replace('.', '');
                    return name.charAt(0).toUpperCase() + name.slice(1);
                });
            } catch (e) {
                return ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
            }
        },

        handleBlur(type) {
            if (type === 'date' && this.dateInput?.length === 10) {
                this.inputValue('date');
            } else if (type === 'time' && this.timeInput?.length === 5) {
                this.inputValue('time');
            }
        },

        inputValue(type) {
            if (type === 'date' && this.dateInput.length === 10) {
                const [day, month, year] = this.dateInput.split('/');
                this.modelDate = new McDate(`${year}-${month}-${day}`)._date;
                this.$emit('update:modelValue', this.modelDate);
            } else if (type === 'time' && this.timeInput.length === 5) {
                const [hours, minutes] = this.timeInput.split(':');
                this.modelTime = {
                    hours: parseInt(hours, 10),
                    minutes: parseInt(minutes, 10),
                    seconds: 0,
                };
                this.$emit('update:modelValue', this.modelTime);
            }
            this.updateDateTime();
        },


        onDateChange(date) {
            this.modelDate = date;
            this.dateInput = new McDate(date).format(this.dateFormat);
            this.$emit('update:modelValue', date);
            this.updateDateTime();
        },

        onTimeChange(time) {
            this.modelTime = time;
            this.timeInput = `${time.hours.toString().padStart(2, '0')}:${time.minutes.toString().padStart(2, '0')}`;
            this.$emit('update:modelValue', time);
            this.updateDateTime();
        },

        updateDateTime() {
            if (this.modelDate && this.fieldType === 'datetime') {
                let datetime = new McDate(this.modelDate)._date;
                datetime.setHours(this.modelTime.hours);
                datetime.setMinutes(this.modelTime.minutes);
                this.$emit('update:modelValue', datetime);
            }
        },
    },
});
