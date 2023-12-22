app.component('entity-table', {
    template: $TEMPLATES['entity-table'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('entity-table')
        return { optionalHeadersSelected: [], messages, text };
    },

    mounted() { },
    data() {
        const visible = this.visible.split(",");
        const required = this.required.split(",");

        const modifiedHeaders = this.headers.map(header => {
            let slug = this.parseSlug(header);

            if (visible.includes(slug) || required.includes(slug)) {
                header.visible = true;
            }

            if (required.includes(slug)) {
                header.required = true;
            }

            return header;
        });

        const activeHeaders = modifiedHeaders
        
        return {
            itemsSelected: Vue.ref([]),
            modifiedHeaders,
            activeHeaders,
            value: '',
            filters: '',
            searchText: '',
            activeItems: this.items,
        }

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
            type: String,
            default: ''
        },
        labelColumn: {
            type: String,
            default: 'nome'
        },
        visible: {
            type: String,
            default: ''
        },
        endpoint: {
            type: String,
            default: 'find'
        },
        statusClasses: {
            type: Object,
            default: () => ({
                '-10': 'row--trash',
                '-2': 'row--archived',
                '-9': 'row--disabled',
                0: 'row--draft',
                1: 'row--enabled row--sent',
                2: 'row--invalid',
                3: 'row--notapproved',
                8: 'row--waitlist',
                10: 'row--approved',
            })
        },
    },

    computed: {
        items() {
            let columns = []
            this.modifiedHeaders.map(function(header) {
                if(!header.required){
                    columns.push(header.text)
                }
            })
            return columns;
        },

        selectedColumns() {
            let columns = []
            this.modifiedHeaders.map(function(header) {
                if(header.visible || header.required){
                    columns.push(header.text)
                }
            })
            return columns;
        },

        optionalHeaders() {
            return this.modifiedHeaders.reduce((columns, header) => {
                if (!header.required) {
                    columns.push(header.text);
                }
                return columns;

            }, []);
        },
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
            this.query['@keyword'] = this.searchText
            entities.refresh(this.watchDebounce);
        },

        removeFromColumns(tag) {
            this.modifiedHeaders.find(header => {
                if(header.text == tag && header.required) {
                    this.messages.error(this.text('item obrigatório') + ' ' + header.text);
                }else if(header.text == tag && header.visible) {
                    header.visible = false;
                }
            })
        },

        addInColumns(tag) {
            this.modifiedHeaders.find(header => {
                if(header.text == tag) {
                    header.visible = true;
                }
            })
        },
    },
});
