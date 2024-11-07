app.component('create-occurrence', {
    template: $TEMPLATES['create-occurrence'],
    emits: ['create'],

    setup() {
        $DESCRIPTIONS.eventoccurrence.day = 
        {
            "isMetadata": false,
            "isEntityRelation": false,
            "required": false,
            "type": "text",
            "length": null,
            "label": ""
        }

        const text = Utils.getTexts('create-occurrence');
        return {
            text,
        }

    },

    data() {
        return {
            locale: $MAPAS.config.locale,
            step: 0,
            free: false,
            space: null,
            newOccurrence: null,
            frequency: 'once',
            startsOn: null,
            startsAt: null,
            endsAt: null,
            duration: 0,
            dateRange: {
                start: null,
                end: null,
            },
            days: {},
            until: null,
            description: null,
            price: null,
            priceInfo: null
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        occurrence: {
            type: Entity,
            default: null
        }
    },

    created() {
        this.newOccurrence = this.occurrence || new Entity('eventoccurrence');
    },

    watch: {
        updateDescription(newVal) {
            this.description = newVal;
        },
    },

    computed: {
        updateDescription() {
            const { start, end } = this.dateRange || { start: '', end: '' };

            if (start) {
                this.startsOn = new McDate(start);
                let endData = end ? new McDate(end) : null;
                let description = '';
                let monthsName = __('meses', 'create-occurrence');
                let daysName = __('dias', 'create-occurrence');
                let daysLength = Object.values(this.days).filter(Boolean).length;

                switch (this.frequency) {
                    case 'once':
                        description += __('uma vez', 'create-occurrence');
                        break;
                    case 'daily':
                        description += __('diariamente', 'create-occurrence');
                        break;
                    case 'weekly':
                        if (this.days[0] == 'on' || (this.days[6] == 'on' && daysLength == 1)) {
                            description += __('todo', 'create-occurrence');
                        } else {
                            description += __('toda', 'create-occurrence');
                        }

                        let count = 1;
                        for (let [key, state] of Object.entries(this.days)) {
                            if (state) {
                                description += daysName[key]
                                ++count;

                                if (daysLength > count) {
                                    description += ', ';
                                }
                                if (daysLength == count) {
                                    description += __('e', 'create-occurrence');
                                }
                            }
                        }
                        break;
                }

                if (this.frequency !== 'once' && endData) {
                    if (this.startsOn.year() != endData.year()) {
                        description += __('anos diferentes', 'create-occurrence');
                    } else {
                        if (this.startsOn.month('numeric') != endData.month('numeric')) {
                            description += __('meses diferentes', 'create-occurrence');
                        } else {
                            description += __('meses iguais', 'create-occurrence');
                        }
                    }
                }

                description = description.replace("{dia}", this.startsOn.day('2-digit'));
                description = description.replace("{mes}", monthsName[this.startsOn.month('numeric') - 1]);
                description = description.replace("{ano}", this.startsOn.year());
                if (endData) {
                    description = description.replace("{diaIni}", this.startsOn.day('2-digit'));
                    description = description.replace("{mesIni}", monthsName[this.startsOn.month('numeric') - 1]);
                    description = description.replace("{anoIni}", this.startsOn.year());
    
                    description = description.replace("{diaFim}", endData.day('2-digit'));
                    description = description.replace("{mesFim}", monthsName[endData.month('numeric') - 1]);
                    description = description.replace("{anoFim}", endData.year());
                }

                if (this.startsAt && this.endsAt) {
                    description += __('das', 'create-occurrence') + String(this.startsAt.hours).padStart(2, '0') + ':' + String(this.startsAt.minutes).padStart(2, '0');
                    description += __('às', 'create-occurrence') + String(this.endsAt.hours).padStart(2, '0') + ':' + String(this.endsAt.minutes).padStart(2, '0');
                } else if (this.startsAt) {
                    if (this.startsAt.hours == '0' || this.startsAt.hours == '1')
                        description += __('à', 'create-occurrence') + String(this.startsAt.hours).padStart(2, '0') + ':' + String(this.startsAt.minutes).padStart(2, '0');
                    else
                        description += __('às', 'create-occurrence') + String(this.startsAt.hours).padStart(2, '0') + ':' + String(this.startsAt.minutes).padStart(2, '0');
                }                

                this.description = description;
                return description;
            }
        }
    },

    methods: {
        // Navegação - mobile
        next() {
            if (this.step < 5) {
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
        moneyMask(valor, locale = 'pt-BR', currency = 'BRL') {
            return new Intl.NumberFormat(locale, { style: 'currency', currency: currency }).format(valor);
        },
        priceMask() {
            let intNum = this.price.split("").filter(s => /\d/.test(s)).join("").padStart(3, "0");
            let floatNum = intNum.slice(0, -2) + "." + intNum.slice(-2);
            this.price = this.moneyMask(floatNum);
        },

        // Criação da ocorrência
        create(modal) {
            this.newOccurrence.eventId = this.entity.id;
            this.newOccurrence.spaceId = this.space ? this.space.id : '';
            this.newOccurrence.space = this.space;
            
            if (this.frequency) {
                this.newOccurrence['frequency'] = this.frequency;

                switch (this.frequency) {
                    case 'once':
                        if (this.startsOn) {
                            this.newOccurrence['startsOn'] = this.startsOn.year() + '-' + (this.startsOn.month('2-digit')) + '-' + this.startsOn.day('2-digit');
                        }
                        break;

                    case 'weekly':
                        if (Object.values(this.days).filter(Boolean).length > 0) {
                            this.newOccurrence['day'] = this.days;
                        }

                        if (this.dateRange) { 
                            let startsOn = new McDate(this.dateRange.start);
                            let endsOn = new McDate(this.dateRange.end);
                            this.newOccurrence['startsOn'] = startsOn.year() + '-' + startsOn.month('2-digit') + '-' + startsOn.day('2-digit');
                            this.newOccurrence['endsOn'] = endsOn.year() + '-' + endsOn.month('2-digit') + '-' + endsOn.day('2-digit');
                            this.newOccurrence['until'] = endsOn.year() + '-' + endsOn.month('2-digit') + '-' + endsOn.day('2-digit');
                        }
                        break;

                    case 'daily':
                        if (this.dateRange) {
                            let startsOn = new McDate(this.dateRange.start);
                            let endsOn = new McDate(this.dateRange.end);
                            this.newOccurrence['startsOn'] = startsOn.year() + '-' + startsOn.month('2-digit') + '-' + startsOn.day('2-digit');
                            this.newOccurrence['until'] = endsOn.year() + '-' + endsOn.month('2-digit') + '-' + endsOn.day('2-digit');
                        }
                        break;
                }
            }

            if (this.startsAt) {
                this.newOccurrence['startsAt'] = String(this.startsAt.hours).padStart(2, "0") + ':' + String(this.startsAt.minutes).padStart(2, "0");
            }

            if (this.endsAt) {
                this.newOccurrence['endsAt'] = String(this.endsAt.hours).padStart(2, "0") + ':' + String(this.endsAt.minutes).padStart(2, "0");
            }

            this.newOccurrence['description'] = this.description ?? '';
            this.newOccurrence['price'] = this.free ? __('Gratuito', 'create-occurrence') : this.price;
            this.newOccurrence['priceInfo'] = this.priceInfo ?? '';
           
            this.newOccurrence.save().then(() => {
                modal.close();
                this.$emit('create', this.newOccurrence);
                this.cleanForm();
            });

        },

        cleanForm() {
            this.step = 0;
            this.free = false;
            this.space = null;
            this.newOccurrence = new Entity('eventoccurrence');
            this.frequency = 'once';
            this.startsOn = null;
            this.startsAt = null;
            this.endsAt = null;
            this.duration = 0;
            this.dateRange = {
                start: null,
                end: null,
            };
            this.days = {};
            this.until = null;
            this.description = null;
            this.price = null;
            this.priceInfo = null;
        },

        cancel(modal) {
            modal.close();
        },

        copyDescription() {
            let description = document.querySelector(".theDescription");
            if (description) {
                navigator.clipboard.writeText(description.innerHTML);
                document.querySelector("input[name='description']").value = description.innerHTML;
                document.querySelector("input[name='description']").focus();
            }
        },

        onChange(event, onInput) {
            if (event instanceof InputEvent) {
                setTimeout(() => onInput(event), 50);
            }
        },
    },
});