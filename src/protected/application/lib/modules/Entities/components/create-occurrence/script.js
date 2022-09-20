app.component('create-occurrence', {
    template: $TEMPLATES['create-occurrence'],
    emits: ['create'],

    setup() {
        const text = Utils.getTexts('create-occurrence');
        return {
            text
        }
    },

    data() {
        var actualDate = new Date();
        return {
            locale: $MAPAS.config.locale,
            entity: null,
            space: null,
            errors: null,
            step: 0,
            autoDescription: '',
            free: false,
            frequency: 'once',
            eventId: 0,
            spaceId: 0,
            startsAt: {
                hours: new Date().getHours(),
                minutes: new Date().getMinutes()
            },
            endsAt: {
                hours: new Date().getHours(),
                minutes: new Date().getMinutes()
            },
            dateRange: null,
            duration: 0,
            startsOn: actualDate.getFullYear() + '-' + (actualDate.getMonth() + 1 < 10 ? '0' + (actualDate.getMonth() + 1) : actualDate.getMonth() + 1) + '-' + actualDate.getDate(),
            until: 0,
            description: '',
            price: 0,
            day: [false, false, false, false, false, false, false],
        }
    },

    props: {
    },

    computed: {

        updateDescription() {

            if (this.dateRange) {
                this.startsOn = this.dateRange[0].substr(0, 4) + '-' + this.dateRange[0].substr(5, 2) + '-' + this.dateRange[0].substr(8, 2)
                this.until = this.dateRange[1].substr(0, 4) + '-' + this.dateRange[1].substr(5, 2) + '-' + this.dateRange[1].substr(8, 2)
            }

            var description = '';
            var months = __('meses', 'create-occurrence');
            var days = __('dias', 'create-occurrence');

            var weekDays = [];
            this.day.forEach(function (status, index) {
                if (status) {
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

            /* Tradução pelo texts.php */
            if (this.frequency == 'once') {
                description += __('uma vez', 'create-occurrence');
            } else {
                if (this.frequency == 'daily') {
                    description += __('diariamente', 'create-occurrence');
                } else if (this.frequency == 'weekly') {
                    if (weekDays[0] == '0' || weekDays[0] == '6') {
                        description += __('semanal 1', 'create-occurrence');
                    } else {
                        description += __('semanal 2', 'create-occurrence');
                    }

                    var count = 1;
                    weekDays.forEach(function (day, index) {
                        description += days[day];
                        count++;
                        if (count == weekDays.length)
                            description += __('e', 'create-occurrence');
                        else if (count < weekDays.length)
                            description += ', ';
                    });
                }

                if (startYear != endYear) {
                    description += __('anos diferentes', 'create-occurrence');
                } else {
                    if (startMonth != endMonth) {
                        description += __('meses diferentes', 'create-occurrence');
                    } else {
                        description += __('meses iguais', 'create-occurrence');
                    }
                }
            }

            description = description.replace("{dia}", startDay);
            description = description.replace("{mes}", months[startMonth]);
            description = description.replace("{ano}", startYear);
            description = description.replace("{diaIni}", startDay);
            description = description.replace("{mesIni}", months[startMonth]);
            description = description.replace("{anoIni}", startYear);
            description = description.replace("{diaFim}", endDay);
            description = description.replace("{mesFim}", months[endMonth]);
            description = description.replace("{anoFim}", endYear);

            if (this.startsAt) {
                if (this.startsAt.hours == '1')
                    description += __('à', 'create-occurrence') + this.startsAt.hours + ':' + this.startsAt.minutes;
                else
                    description += __('às', 'create-occurrence') + this.startsAt.hours + ':' + this.startsAt.minutes;
            }

            this.autoDescription = description;
        }
    },

    methods: {

        cancel(modal) {
            modal.close();
        },

        // Navegação mobile
        next() {
            if (this.step < 5) {
                if (this.step == 4)
                    this.updateDescription();
                ++this.step
            }
        },
        prev() {
            if (this.step > 0) {
                --this.step;
            }
        },

        // Seleção do espaço vinculado
        selectSpace(space) {
            this.space = space;
        },
        removeSpace() {
            this.space = null;
        },

        // Máscara monetária 
        mascaraMoeda (event) {            
            const intNum = event.target.value.split("").filter(s => /\d/.test(s)).join("").padStart(3, "0");
            const floatNum = intNum.slice(0, -2) + "." + intNum.slice(-2);
            event.target.value = this.FormataValor(floatNum);
        },
        FormataValor(valor, locale = 'pt-BR', currency = 'BRL') {
            return new Intl.NumberFormat(locale, {style: 'currency', currency}).format(valor);
        },

        create() {

            /*  new entity
                popular entidade
                Verificar erros
                Emitir o evento 'create' em caso de sucesso
                entity save 
                Popular o this.errors em caso de fracasso
            */

            this.entity = new Entity('eventOccurrence');

            /*
            this.entity.count           =
            this.entity.event           =
            this.entity.eventId         =
            this.entity.frequency       =
            this.entity.id              =
            this.entity.rule            =
            this.entity.separation      =
            this.entity.space           =
            this.entity.spaceId         =
            this.entity.status          =
            this.entity.timezoneName    =   
            this.entity._endsAt         =
            this.entity._endsOn         =
            this.entity._startsAt       =
            this.entity._startsOn       =
            this.entity._until          =
            */

        }

    },
});