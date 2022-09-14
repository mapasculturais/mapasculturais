app.component('create-occurrence' , {
    template: $TEMPLATES['create-occurrence'],
    emits: ['create'],

    setup() { 
      
    },

    created() {
        this.createEntity()
    },

    data() {
        var actualDate = new Date();
        return {
            entity: null,
            step: 0,
            autoDescription: '',
            free: false,
            frequency: '',
            eventId: 0,
            spaceId: 0,
            startsAt: '00:00',
            duration: 0,
            endsAt: '00:00',
            startsOn: actualDate.getFullYear()+'-'+(actualDate.getMonth()+1 < 10 ? '0'+(actualDate.getMonth()+1) : actualDate.getMonth()+1)+'-'+actualDate.getDate(),
            until: 0,
            description: '',
            price: 0,
            day: [false, false, false, false, false, false, false],

        }
    },

    props: {
        /* entity: {
            type: Entity,
            required: true
        }, */

        editable: {
            type: Boolean,
            default: false,
        },
    },
    
    methods: {
        
        cancel(modal) {
            modal.close();
        },

        next() {
            if(this.step < 5) {
                if (this.step == 4)
                    this.updateDescription();
                ++this.step
            }
        },

        prev() {
            if(this.step > 0) {
                --this.step;
            }
        },

        createEntity() {
            this.entity = new Entity('new-occurrence');
        },

        updateDescription() {
            var description = '';
            var month = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            var days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

            var weekDays = [];
            this.day.forEach(function(status, index) {
                if(status) {
                    weekDays.push(index);
                }
            });

            var startData = new Date(this.startsOn + 'T00:00');
            var startDay = startData.getDate();
            var startMonth = (startData.getMonth() + 1);
            var startYear = startData.getFullYear();

            var endData = new Date(this.until + 'T00:00');
            var endDay = endData.getDate();
            var endMonth = (endData.getMonth() + 1);
            var endYear = endData.getFullYear();

            if (this.frequency == 'once') {
                description += "Dia " + startDay + " de " + month[startMonth] + " de " + startYear;
            } else {
                if (this.frequency == 'daily') {
                    description += 'Diariamente, ';
                } else if (this.frequency == 'weekly') {
                    if (weekDays[0] == '0' || weekDays[0] == '6') {
                        description += 'Todo ';
                    } else {
                        description += 'Toda ';
                    }

                    var count = 1;
                    weekDays.forEach(function(day, index) {
                        description += days[day];
                        count ++;
                        if (count == weekDays.length)
                            description += ' e ';
                        else if (count < weekDays.length)
                            description += ', '
                    });
                }

                if (startYear != endYear) {
                    description += ' de ' + startDay + " de " + month[startMonth] + " de " + startYear;
                    description += ' a ' + endDay + " de " + month[endMonth] + " de " + endYear;
                } else {
                    if (startMonth != endMonth) {
                        description += ' de ' + startDay + " de " + month[startMonth] + ' a ' + endDay + " de " + month[endMonth];
                    } else {
                        description += ' de ' + startDay + ' a ' + endDay + " de " + month[endMonth] + " de " + endYear;
                    }
                }
            }

            if (this.startsAt) {
                if (this.startsAt.substring(0,2) == '01')
                    description += ' à ' + this.startsAt;
                else
                    description += ' às ' + this.startsAt;
            }

            this.autoDescription = description;
        },

        create() {

            /* data = {
                eventId: this.eventId,
                spaceId: this.spaceId,
                startsAt: this.startsAt,
                duration: this.duration,
                endsAt: this.endsAt,
                frequency: this.frequency,
                startsOn: this.startsOn,
                until: this.until,
                day: this.day,
                description: this.description,
                price: this.price,
            } */

        }
       
    },
});
