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

        controller: {
            type: String
        },

        select: String,
        limit: {
            type: Number,
            default: 50
        },
        order: {
            type: String,
            default: 'createTimestamp ASC'
        },
        query: {
            type: Object || String,
            default: {}
        },
        watchDebounce: {
            type: Number,
            default: 1500
        },
        headers: {
            type: Array,
            required: true
        },
        required: {
            type: [String, Array],
            default: ''
        },
        visible: {
            type: [String, Array],
            default: ''
        },
        endpoint: {
            type: String,
            default: 'find'
        },
        showIndex: {
            type: Boolean,
            default: false
        },
        sortOptions: {
            type: Array,
            default: [
                { order: 'createTimestamp DESC', label: __('mais recentes primeiro', 'entity-table') },
                { order: 'createTimestamp ASC',  label: __('mais antidas primeiro', 'entity-table') },
                { order: 'updateTimestamp DESC', label: __('modificadas recentemente', 'entity-table') },
                { order: 'updateTimestamp ASC',  label: __('modificadas há mais tempo', 'entity-table') },
            ]
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
            apiController: this.controller || this.type,
            entitiesOrder: this.order,
            columns: this.headers,
            searchText: '',
        }
    },

    computed: {
        visibleColumns() {
            return this.columns.filter((col) => col.visible);
        },
        $description() {
            return $DESCRIPTION[this.type];
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

        resetHeaders() {
            const visible = this.visible instanceof Array ? this.visible : this.visible.split(",");
            const required = this.required instanceof Array ? this.required : this.required.split(",");
            
            for (let header of this.columns) {
                if(visible.includes(header.slug) || required.includes(header.slug)) {
                    header.visible = true;
                } else {
                    header.visible = false;
                }
            }
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

        showAllHeaders() {
            if (!this.$refs.allHeaders.checked) {
                this.resetHeaders();
            } else {
                for (let header of this.columns) {
                    header.visible = true;
                }
            }
        },

        clearFilters(entities) {
            this.resetHeaders();
            this.$refs.allHeaders.checked = false;

            if(this.searchText != '') {
                this.searchText = '';
                delete this.query['@keyword'];
                entities.refresh(this.watchDebounce);
            }

            this.$emit('clear-filters', entities);
        }
    },
});
