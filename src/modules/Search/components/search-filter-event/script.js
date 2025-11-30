app.component('search-filter-event', {
    template: $TEMPLATES['search-filter-event'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-filter-event');
        const parseDate = (date) => {
            if (date instanceof Date) {
                return new Date((new McDate(date)).date('sql') + ' 12:00:00');
            } else {
                return new Date(date.substr(0, 10) + ' 12:00:00');
            }
        }

        const toRangeString = (date) => {
            const d0 = parseDate(date[0]);
            const d1 = parseDate(date[1]);
            return d0.toDateString() + ':' + d1.toDateString();
        }

        const today = parseDate(new Date());
        const nextmonth = parseDate(Dates.addMonths(today, 1));
        const mctoday = new McDate(today);
        const mcnextmonth = new McDate(nextmonth);
        const startOfWeek = parseDate(Dates.startOfWeek(today));
        const endOfWeek = parseDate(Dates.nextSaturday(today));
        const startOfMonth = parseDate(Dates.startOfMonth(today));
        const endOfMonth = parseDate(Dates.endOfMonth(today));

        const nextFriday = parseDate(Dates.nextFriday(startOfWeek));
        const nextSunday = parseDate(Dates.nextSunday(startOfWeek));

        const ranges = {
            yesterday: {
                label: __('ontem', 'search-filter-event'),
                range: [Dates.subDays(today, 1), Dates.subDays(today, 1)]
            },
            today: {
                label: __('hoje', 'search-filter-event'),
                range: [today, today]
            },
            tomorrow: {
                label: __('amanhã', 'search-filter-event'),
                range: [Dates.addDays(today, 1), Dates.addDays(today, 1)]
            },
            lastWeek: {
                label: __('semana passada', 'search-filter-event'),
                range: [Dates.subWeeks(startOfWeek, 1), Dates.subWeeks(endOfWeek, 1)]
            },
            thisWeek: {
                label: __('esta semana', 'search-filter-event'),
                range: [startOfWeek, endOfWeek]
            },
            nextWeek: {
                label: __('próxima semana', 'search-filter-event'),
                range: [Dates.addWeeks(startOfWeek, 1), Dates.addWeeks(endOfWeek, 1)]
            },
            lastWeekend: {
                label: __('fim de semana passado', 'search-filter-event'),
                range: [Dates.subWeeks(nextFriday, 1), Dates.subWeeks(nextSunday, 1)]
            },
            thisWeekend: {
                label: __('este fim de semana', 'search-filter-event'),
                range: [nextFriday, nextSunday]
            },
            nextWeekend: {
                label: __('próximo fim de semana', 'search-filter-event'),
                range: [Dates.addWeeks(nextFriday, 1), Dates.addWeeks(nextSunday, 1)]
            },
            last7days: {
                label: __('últimos 7 dias', 'search-filter-event'),
                range: [Dates.subDays(today, 7), today]
            },
            next7days: {
                label: __('próximos 7 dias', 'search-filter-event'),
                range: [today, Dates.addDays(today, 7)]
            },
            last30days: {
                label: __('últimos 30 dias', 'search-filter-event'),
                range: [Dates.subDays(today, 30), today]
            },
            next30days: {
                label: __('próximos 30 dias', 'search-filter-event'),
                range: [today, Dates.addDays(today, 30)]
            },
            thisCurrentMonth: {
                label: __('este mês', 'search-filter-event'),
                range: [startOfMonth, endOfMonth],
            },
            thisMonth: {
                label: Utils.ucfirst(mctoday.month()),
                range: [startOfMonth, endOfMonth],
            },
            nextMonth: {
                label: Utils.ucfirst(mcnextmonth.month()),
                range: [parseDate(Dates.startOfMonth(nextmonth)), parseDate(Dates.endOfMonth(nextmonth))],
            },
            thisYear: {
                label: Utils.ucfirst(mctoday.year()),
                range: [parseDate(Dates.startOfYear(today)), parseDate(Dates.endOfYear(today))],
            }
        }

        return { text, parseDate, toRangeString, ranges }
    },

    beforeCreate() {
        this.defaultDateFrom = this.parseDate(this.pseudoQuery['@from']);
        this.defaultDateTo = this.parseDate(this.pseudoQuery['@to']);
        this.pseudoQuery['event:name'] = this.pseudoQuery['event:name'] || [];
        this.pseudoQuery['event:term:linguagem'] = this.pseudoQuery['event:term:linguagem'] || [];
        this.pseudoQuery['event:classificacaoEtaria'] = this.pseudoQuery['event:classificacaoEtaria'] || [];
        this.pseudoQuery['@seals'] = this.pseudoQuery['@seals'] || [];
        this.pseudoQuery['En_Estado'] = this.pseudoQuery['En_Estado'] || [];
        this.pseudoQuery['En_Municipio'] = this.pseudoQuery['En_Municipio'] || [];
    },

    props: {
        position: {
            type: String,
            default: 'list'
        },
        pseudoQuery: {
            type: Object,
            required: true
        },
    },

    watch: {
        "pseudoQuery['@seals']": {
            handler(value) {
                if (value && value.length > 0) {
                    this.query['@seals'] = value.map(v => v.value || v).join(',');
                } else {
                    delete this.query['@seals'];
                }
                this.$emit('filter-changed', this.query);
            },
            deep: true,
        },
        "pseudoQuery['En_Estado']": {
            handler(value) {
                // this.selectedStates = value;
                this.filterByState();
                this.$emit('changeStates', value);
            },
            deep: true,
        },
        "pseudoQuery['En_Municipio']": {
            handler(value) {
                // this.selectedCities = value;
                this.filterByCities();
                this.$emit('changeCities', value);
            },
            deep: true,
        },

        date: {
            handler(date) {
                if (!date) {
                    this.date = [this.defaultDateFrom, this.defaultDateTo];
                    return;
                }

                const d0 = new McDate(new Date(date[0]));
                const d1 = new McDate(new Date(date[1]));
                this.pseudoQuery['@from'] = d0.date('sql');
                this.pseudoQuery['@to'] = d1.date('sql');
            },
            deep: true,
        },
    },

    computed: {
        states() {
            return Object.fromEntries(
                Object.entries(this.statesAndCities).map(([uf, estado]) => [uf, estado.label])
            );
        },
        cities() {
            const estadosSelecionados = this.pseudoQuery['En_Estado'] || [];
            let cidades = {};

            estadosSelecionados.forEach(uf => {
                if (this.statesAndCities[uf]?.cities) {
                    this.statesAndCities[uf].cities.forEach(cidade => {
                        cidades[cidade] = estadosSelecionados.length > 1 ? `${cidade} - ${uf}` : cidade;
                    });
                }
            });

            return cidades;
        },
    },

    data() {
        const presetRanges = [
            this.ranges.today,
            this.ranges.tomorrow,
            this.ranges.thisWeek,
            this.ranges.thisWeekend,
            this.ranges.thisCurrentMonth,
            this.ranges.nextWeekend,
            this.ranges.next7days,
            this.ranges.next30days,
            this.ranges.nextMonth,
            this.ranges.thisYear,
        ];

        let query = {
            '@select': 'id,name,subTitle,files.avatar,seals.name,seals.id,terms,classificacaoEtaria,singleUrl',
            '@order': 'createTimestamp DESC',
            '@limit': 20,
            '@page': 1,
        }

        return {
            locale: $MAPAS.config.locale,
            terms: $TAXONOMIES.linguagem.terms,
            query,
            date: [this.defaultDateFrom, this.defaultDateTo],
            presetRanges: presetRanges,
            ageRating: $DESCRIPTIONS.event.classificacaoEtaria.optionsOrder,
            sealsNames: $MAPAS.config.event_filters.seals.map(seal => ({
                value: seal.id,
                label: seal.name
            })),
            statesAndCities: $MAPAS.config.statesAndCities,
            selectedStates: [],
            selectedCities: [],
            sealsLabels: Object.fromEntries($MAPAS.config.event_filters.seals.map(seal => [seal.id, seal.name])),
        }
    },

    methods: {
        filterByState() {
            if (this.selectedStates.length > 0) {
                this.query['En_Estado'] = `IN(${this.selectedStates.toString()})`;
            } else {
                delete this.query['En_Estado'];
            }
            this.$emit('filter-changed', this.query);
        },

        filterByCities() {
            if (this.selectedCities.length > 0) {
                this.query['En_Municipio'] = `IN(${this.selectedCities.toString()})`;
            } else {
                delete this.query['En_Municipio'];
            }
            this.$emit('filter-changed', this.query);
        },

        clearFilters() {
            this.selectedStates = [];
            this.selectedCities = [];

            const types = ['string', 'boolean'];
            this.date = [this.defaultDateFrom, this.defaultDateTo];
            for (const key in this.pseudoQuery) {
                if (['@from', '@to'].includes[key]) {
                    this.pseudoQuery[key] = new McDate(new Date(key == '@from' ? this.date[0] : this.date[1])).date('sql');
                } else {
                    if (Array.isArray(this.pseudoQuery[key])) {
                        this.pseudoQuery[key] = [];
                    } else if (types.includes(typeof this.pseudoQuery[key])) {
                        delete this.pseudoQuery[key];
                    }
                }
            }
            this.$emit('filter-changed');
        },

        dateFormat(date) {
            const d0 = new Date(date[0]);
            const d1 = new Date(date[1]);
            const mcd0 = new McDate(d0);
            const mcd1 = new McDate(d1);
            const d0s = mcd0.date('2-digit')
            const d1s = mcd1.date('2-digit');

            // imprime o ano
            if (this.isFirstDayOfYear(d0) && this.isLastDayOfYear(d1)) {
                return mcd0.year();

                // imprime o mês
            } else if (Dates.isFirstDayOfMonth(d0) && Dates.isLastDayOfMonth(d1)) {
                const mctoday = new McDate(new Date);
                const thisYear = mctoday.year() == mcd0.year();

                if (thisYear) {
                    return Utils.ucfirst(mcd0.month());
                } else {
                    return Utils.ucfirst(mcd0.month()) + ', ' + mcd0.year();
                }
            }

            // imprime um preset
            const rangeString = this.toRangeString(date);
            for (let preset of Object.values(this.ranges)) {
                if (this.toRangeString(preset.range) == rangeString) {
                    return preset.label;
                }
            }

            // imprime as datas selecionadas
            if (d0s == d1s) {
                return d0s;
            } else {
                return `${d0s} - ${d1s}`;
            }
        },

        nextInterval() {
            const d0 = this.parseDate(this.date[0]);
            const d1 = this.parseDate(this.date[1]);

            // trocando o ano
            if (this.isFirstDayOfYear(d0) && this.isLastDayOfYear(d1)) {

                const firstDay = Dates.addYears(d0, 1);
                this.date = [
                    firstDay,
                    Dates.lastDayOfYear(firstDay)
                ];

                // trocando o mês
            } else if (Dates.isFirstDayOfMonth(d0) && Dates.isLastDayOfMonth(d1)) {
                const firstDay = Dates.addMonths(d0, 1);
                this.date = [
                    firstDay,
                    Dates.lastDayOfMonth(firstDay)
                ];

                // trocando a semana
            } else if (Dates.isSunday(d0) && Dates.isSaturday(d1)) {
                const firstDay = Dates.addWeeks(d0, 1);
                this.date = [
                    firstDay,
                    Dates.lastDayOfWeek(firstDay)
                ];

                // trocando o fim de semana
            } else if (Dates.isFriday(d0) && Dates.isSunday(d1)) {
                this.date = [
                    Dates.addWeeks(d0, 1),
                    Dates.addWeeks(d1, 1),
                ];

                // trocando o dia
            } else if (Dates.isSameDay(d0, d1)) {
                this.date = [
                    Dates.addDays(d0, 1),
                    Dates.addDays(d1, 1),
                ];

                // outros intervalos
            } else {
                const diff = Dates.differenceInDays(d1, d0);
                this.date = [
                    d1,
                    Dates.addDays(d1, diff)
                ];
            }
        },

        prevInterval() {
            const d0 = this.parseDate(this.date[0]);
            const d1 = this.parseDate(this.date[1]);

            // trocando o ano
            if (this.isFirstDayOfYear(d0) && this.isLastDayOfYear(d1)) {
                const firstDay = Dates.subYears(d0, 1);
                this.date = [
                    firstDay,
                    Dates.subDays(d0, 1)
                ];

                // trocando o mês
            } else if (Dates.isFirstDayOfMonth(d0) && Dates.isLastDayOfMonth(d1)) {
                const firstDay = Dates.subMonths(d0, 1);
                this.date = [
                    firstDay,
                    Dates.subDays(d0, 1)
                ];

                // trocando a semana
            } else if (Dates.isSunday(d0) && Dates.isSaturday(d1)) {
                this.date = [
                    Dates.subWeeks(d0, 1),
                    Dates.subWeeks(d1, 1)
                ];

                // trocando o fim de semana
            } else if (Dates.isFriday(d0) && Dates.isSunday(d1)) {
                this.date = [
                    Dates.subWeeks(d0, 1),
                    Dates.subWeeks(d1, 1),
                ];

                // trocando o dia
            } else if (Dates.isSameDay(d0, d1)) {
                this.date = [
                    Dates.subDays(d0, 1),
                    Dates.subDays(d1, 1),
                ];

                // outros intervalos
            } else {
                const diff = Dates.differenceInDays(d1, d0);
                this.date = [
                    Dates.subDays(d0, diff),
                    d0
                ];

            }
        },

        isFirstDayOfYear(date) {
            return date.toDateString() == Dates.startOfYear(date).toDateString();
        },

        isLastDayOfYear(date) {
            return date.toDateString() == Dates.endOfYear(date).toDateString();

        }
    },
});
