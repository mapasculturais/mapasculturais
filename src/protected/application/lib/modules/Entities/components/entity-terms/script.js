app.component('entity-terms', {
    template: $TEMPLATES['entity-terms'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-terms')
        return { text }
    },

    beforeCreate() {
        this.definition = $TAXONOMIES[this.taxonomy];

        if (!this.definition) {
            throw Error(`Taxonomia ${this.taxonomy} não registrada na aplicação`);
        }

        this.entity.terms[this.taxonomy] = this.entity.terms[this.taxonomy] || [];
    },

    data() {
        return {
            allowInsert: this.definition.allowInsert,
            terms: this.definition.terms || [],
            label: this.title || this.definition.name,
            entityTerms: Vue.ref(this.entity.terms[this.taxonomy]),

            filter: '',
        };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: ''
        },
        taxonomy: {
            type: String,
            required: true
        },
        editable: {
            type: Boolean,
            default: false
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        popoverTitle: {
            type: String,
            default: ''
        },
    },

    computed: {
        filteredTerms() {
            if (this.allowInsert && this.filter.trim().length == 0) {
                return [];
            }

            return this.terms.filter((term) => {
                const _filter = this.filter.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                const _term = term.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

                if (_term.indexOf(_filter) >= 0) {
                    return term;
                }
            })
        }
    },

    methods: {
        insertTag(toggleModal) {
            this.addTerm(this.filter);
            this.filter = '';
            toggleModal();
        },
        loadTerms() {
            if (this.definition.terms.length == 0) {
                const api = new API('term');
                const response = api.GET('api/list/' + this.taxonomy);
                response.then((r) => r.json().then((r) => {
                    this.definition.terms = r;
                    this.terms = r;
                }));
            }
        },

        highlightedTerm(term) {
            const _filter = this.filter.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const _term = term.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const indexOf = _term.indexOf(_filter);
            if (indexOf >= 0) {
                const part0 = term.substr(0, indexOf);
                const part1 = term.substr(indexOf, this.filter.length);
                const part2 = term.substr(indexOf + this.filter.length);
                return `${part0}<b><u>${part1}</u></b>${part2}`;
            } else {
                return term;
            }
        },


        addTerm(term) {
            if (this.entityTerms.indexOf(term) < 0) {
                this.entityTerms.push(term);
            }

            if (this.terms.indexOf(term) < 0) {
                this.terms.push(term);
            }
           
        }
    }
});
