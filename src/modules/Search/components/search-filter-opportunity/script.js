app.component('search-filter-opportunity', {
    template: $TEMPLATES['search-filter-opportunity'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-filter-opportunity')
        return { text }
    },

    props: {
        position: {
            type: String,
            default: 'list'
        },
        pseudoQuery: {
            type: Object,
            required: true,
        }
    },

    data() {
        return {
            terms: $TAXONOMIES.area.terms,
            types: $DESCRIPTIONS.opportunity.type.options,
            selectedTypes: [],

        }
    },

    methods: {
        clearFilters() {
            const types = ['string', 'boolean'];
            for (const key in this.pseudoQuery) {
                if (Array.isArray(this.pseudoQuery[key])) {
                    this.pseudoQuery[key] = [];
                } else if (types.includes(typeof this.pseudoQuery[key])) {
                    delete this.pseudoQuery[key];
                }
            }   
            this.$refs.form.reset();
        },
        actualDate() {
            var data = new Date();
            var dia = String(data.getDate()).padStart(2, '0');
            var mes = String(data.getMonth() + 1).padStart(2, '0');
            var ano = data.getFullYear();

            return (ano + '-' + mes + '-' + dia);
        },

        futureDate() {
            var date = this.actualDate();
            var futureDate = new Date(date.replace(/\-/gi, ', '));
            futureDate.setMonth(futureDate.getMonth() + (1));

            var dia = String(futureDate.getDate()).padStart(2, '0');
            var mes = String(futureDate.getMonth() + 1).padStart(2, '0');
            var ano = futureDate.getFullYear();

            return (ano + '-' + mes + '-' + dia);
        },

        openForRegistrations() {
            this.pseudoQuery['registrationFrom'] = '<= ' + this.actualDate();
            this.pseudoQuery['registrationTo'] = '>= ' + this.actualDate();
        },

        closedForRegistrations() {
            this.pseudoQuery['registrationTo'] = '< ' + this.actualDate();
            delete this.pseudoQuery.registrationFrom;
        },

        futureRegistrations() {
            this.pseudoQuery['registrationFrom'] = '> ' + this.actualDate();
            delete this.pseudoQuery.registrationTo;
        }
    },
});
