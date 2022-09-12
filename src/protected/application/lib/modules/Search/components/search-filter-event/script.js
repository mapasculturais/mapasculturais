app.component('search-filter-event', {
    template: $TEMPLATES['search-filter-event'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-filter-event')
        return { text }
    },

    props: {
        position: {
            type: String,
            default: 'list'
        },
        pseudoQuery: {
            type: Object,
            required: true
        }
    },

    watch: {
        date: {
            handler(date){
                console.log(date);
                const d0 = new McDate(new Date(date[0]));
                const d1 = new McDate(new Date(date[1]));
                this.pseudoQuery['@from'] = d0.date('sql');
                this.pseudoQuery['@to'] = d1.date('sql');
            },
            deep: true,
        }
    },

    data() {
        const from = new Date(this.pseudoQuery['@from'] + ' UTC');
        const to = new Date(this.pseudoQuery['@to'] + ' UTC');

        const today = new Date();
        const nextmonth = Dates.addMonths(today,1);
        const mctoday = new McDate(today);
        const mcnextmonth = new McDate(nextmonth);
        const nextFriday = Dates.nextFriday(Dates.startOfWeek(new Date()));
        const nextSunday = Dates.nextSunday(Dates.startOfWeek(new Date()))
        const presetRanges = [
            { 
                label: this.text('hoje'), 
                range: [today, today]   
            },
            { 
                label: this.text('amanhã'), 
                range: [Dates.addDays(today,1), Dates.addDays(today,1)] 
            },
            { 
                label: this.text('esta semana'), 
                range: [Dates.startOfWeek(today), Dates.endOfWeek(today)]
            },
            { 
                label: this.text('próxima semana'), 
                range: [Dates.addWeeks(Dates.startOfWeek(today),1), Dates.addWeeks(Dates.endOfWeek(today),1)]
            },
            { 
                label: this.text('este fim de semana'), 
                range: [nextFriday, nextSunday]
            },
            { 
                label: this.text('próximo fim de semana'), 
                range: [Dates.addWeeks(nextFriday,1), Dates.addWeeks(nextSunday,1)]
            },
            {
                label: Utils.ucfirst(mctoday.month()),
                range: [Dates.startOfMonth(Dates.startOfMonth(today)), Dates.endOfMonth(today)],
            },
            {
                label: Utils.ucfirst(mcnextmonth.month()),
                range: [Dates.startOfMonth(Dates.startOfMonth(nextmonth)), Dates.endOfMonth(nextmonth)],
            },
            {
                label: Utils.ucfirst(mctoday.year()),
                range: [Dates.startOfYear(today), Dates.endOfYear(today)],
            },
          ];


        return {
            terms: $TAXONOMIES.linguagem.terms,
            date: [from, to],
            presetRanges: presetRanges
        }
    },

    computed: {
    },
    
    methods: {
        toRangeString(date) {
            const d0 = date[0];
            const d1 = date[1];
            return d0.toDateString() + ':' + d1.toDateString();
        },

        dateFormat(date) {
            const d0 = new Date(date[0]);
            const d1 = new Date(date[1]);
            const mcd0 = new McDate(new Date(date[0] + ' UTC'));
            const mcd1 = new McDate(new Date(date[1] + ' UTC'));
            const d0s = mcd0.date('2-digit')
            const d1s = mcd1.date('2-digit');

            // imprime o ano
            if(this.isFirstDayOfYear(d0) && this.isLastDayOfYear(d1)) {
                return mcd0.year();

            // imprime o mês
            } else if (Dates.isFirstDayOfMonth(d0) && Dates.isLastDayOfMonth(d1)) {
                const mctoday = new McDate(new Date);
                const thisYear = mctoday.year() == mcd0.year();

                if(thisYear) {
                    return Utils.ucfirst(mcd0.month());
                } else {
                    return Utils.ucfirst(mcd0.month()) + ', ' + mcd0.year();
                }
            }

            // imprime um preset
            const rangeString = this.toRangeString(date);
            for(let preset of this.presetRanges) {
                if (this.toRangeString(preset.range) == rangeString) {
                    return preset.label;
                }
            }

            // imprime as datas selecionadas
            if(d0s == d1s) {
                return d0s;    
            } else {
                return `${d0s} - ${d1s}`;
            }
        },

        nextInterval() {
            const d0 = new Date(this.date[0]);
            const d1 = new Date(this.date[1]);

            // trocando o ano
            if(this.isFirstDayOfYear(d0) && this.isLastDayOfYear(d1)) {
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

            // trocando o fim de semana
            } else if (Dates.isFriday(d0) && Dates.isSunday(d1)) {
                this.date = [
                    Dates.addWeeks(d0,1),
                    Dates.addWeeks(d1,1),
                ];

            // outros intervalos
            } else {
                const diff = Dates.differenceInDays(d1,d0);
                this.date = [
                    Dates.addDays(d1,1),
                    Dates.addDays(d1, diff+1)
                ];
            }
        },

        prevInterval() {

            const d0 = new Date(this.date[0]);
            const d1 = new Date(this.date[1]);
            
            // trocando o ano
            if(this.isFirstDayOfYear(d0) && this.isLastDayOfYear(d1)) {
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

            // trocando o fim de semana
            } else if (Dates.isFriday(d0) && Dates.isSunday(d1)) {
                this.date = [
                    Dates.subWeeks(d0,1),
                    Dates.subWeeks(d1,1),
                ];

            // outros intervalos
            } else {
                const diff = Dates.differenceInDays(d1,d0);
                this.date = [
                    Dates.subDays(d0, diff+1), 
                    Dates.subDays(d0,1)
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
