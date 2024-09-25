app.component('mc-datepicker', {
    template: $TEMPLATES['mc-datepicker'],
    emits: ['update:modelDate', 'update:modelTime'],

    props: {
        fieldType: {
            type: String,
            required: true,
            validator: (value) => ['date', 'time'].includes(value),
        },

        locale: {
            type: String,
            default: 'pt-BR'
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
            modelTime: '',
        }
    },

    computed: {
        isDateType() {
            return this.fieldType === 'date';
        },

        isTimeType() {
            return this.fieldType === 'time';
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
                this.$emit('update:modelDate', this.modelDate);
            } else if (type === 'time' && this.timeInput.length === 5) {
                const [hours, minutes] = this.timeInput.split(':');
                this.modelTime = {
                    hours: parseInt(hours, 10),
                    minutes: parseInt(minutes, 10),
                    seconds: 0,
                };
                this.$emit('update:modelTime', this.modelTime);
            }
        },


        onDateChange(date) {
            this.modelDate = date;
            this.dateInput = new McDate(date).format(this.dateFormat);
            this.$emit('update:modelDate', date);
        },

        onTimeChange(time) {
            this.modelTime = time;
            this.timeInput = `${time.hours.toString().padStart(2, '0')}:${time.minutes.toString().padStart(2, '0')}`;
            this.$emit('update:modelTime', time);
        },
    },
});
