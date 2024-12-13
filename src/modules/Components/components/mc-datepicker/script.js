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
            default: 'pt-BR'
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
            dayNames: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
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
