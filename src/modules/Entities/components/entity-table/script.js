app.component('entity-table', {
    template: $TEMPLATES['entity-table'],
    emits: ['clear-filters'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        const messages = useMessages();
        const text = Utils.getTexts('entity-table')
        return { messages, text, hasSlot};
    },

    props: {
        type: {
            type: String,
            required: true
        },
        select: String,
        limit: Number,
        query: {
            type: Object || String,
            default: {}
        },
        watchDebounce: {
            type: Number,
            default: 500
        },
        headers: {
            type: Array,
            required: true
        },
        required: {
            type: [String, Array],
            default: ''
        },
        labelColumn: {
            type: String,
            default: 'nome'
        },
        visible: {
            type: [String, Array],
            default: ''
        },
        endpoint: {
            type: String,
            default: 'find'
        },
    },

    created() {
        const visible = this.visible instanceof Array ? this.visible : this.visible.split(",");
        const required = this.required instanceof Array ? this.required : this.required.split(",");
        
        for(let header of this.columns) {
            header.slug = this.parseSlug(header);
            header.visible = visible.includes(header.slug) || required.includes(header.slug);
            header.required = required.includes(header.slug);
        }
    },

    data() {
        return {
            columns: this.headers,
            searchText: '',
        }
    },

    methods: {
        parseSlug(header) {
            if (header.slug) {
                return header.slug
            }

            return header.value
        },

        getEntityData(obj, value) {
            let val = eval(`obj.${value}`);

            if(val instanceof McDate) {
                val = val.date('numeric year')
            }

            return val;
        },

        keyword(entities) {
            window.dispatchEvent(new CustomEvent('entityTableSearchText', { detail: {searchText: this.searchText} }));
            this.query['@keyword'] = this.searchText
            entities.refresh(this.watchDebounce);
        },

        toggleHeaders(event) {
            for (let header of this.columns) {
                if (header.slug == event.target.value) {
                    if (header.required) {
                        event.preventDefault();
                        event.stopPropagation();
                        this.messages.error(this.text('item obrigatório') + ' ' + header.text);
                    } else {
                        header.visible = !header.visible;
                    }
                }
            }
        },

        clearFilters(entities) {
            const visible = this.visible instanceof Array ? this.visible : this.visible.split(",");
            const required = this.required instanceof Array ? this.required : this.required.split(",");

            for (let header of this.columns) {
                if(visible.includes(header.slug) || required.includes(header.slug)) {
                    header.visible = true;
                } else {
                    header.visible = false;
                }
            }

            this.searchText = '';
            delete this.query['@keyword'];
            entities.refresh(this.watchDebounce);

            this.$emit('clear-filters', entities);
        }
    },
});
