app.component('entity-table', {
    template: $TEMPLATES['entity-table'],

    setup({ headers }, { slots }) {
        const activeSlots = Object.keys(slots)
        const hasSlot = name => !!slots[name];
        const activeHeaders = Vue.ref(headers.filter(
            header => header.required
        ));
        return { activeHeaders, optionalHeadersSelected: [], hasSlot, activeSlots };
    },
    data() {

        return {
            itemsSelected: Vue.ref([]),
        }
    },

    props: {
        headers: {
            type: Array,
            required: true
        },
        // requiredColumns: {
        //     type: String,
        //     default: ''
        // },
        // optionalColumns: {
        //     type: String,
        //     default: ''
        // },
        // visibleColumns: {
        //     type: String,
        //     default: ''
        // },
        items: {
            type: Array,
            required: true
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
        activeColumns() {
            return this.activeHeaders.map(header => (header.value));
        },

        visibleColumns() {
            return this.activeHeaders.reduce((columns, header) => {
                if (!header.required) {
                    columns.push(header);
                }
                return columns;
            }, []);
        },
        selectedColumns() {
            return this.activeHeaders.reduce((columns, header) => {
                if (!header.required) {
                    columns.push(header.text);
                }
                return columns;
            }, []);
        },

        optionalHeaders() {
            return this.headers.reduce((columns, header) => {
                if (!header.required) {
                    columns.push(header);
                }
                return columns;
            }, []);
            // return this.headers.filter(header => !header.required);
        },
    },

    methods: {
        removeFromColumns(tag) {
            if (this.activeColumns.includes(tag)) {
                this.activeHeaders = this.activeHeaders.filter(header => header.value != tag);
            }
        },
        addInColumns(tag) {
            console.log(this.optionalHeaders.includes(tag));
            console.log(this.optionalHeaders.find(header =>header.value == tag));
            if (!this.activeColumns.includes(tag)) {
                this.activeHeaders.push(this.optionalHeaders.find(header =>header.value == tag));
            }
        },
        customRowClassName(item) {
            return this.statusClasses[item.status];

        },

        isActive(column) {
            return this.activeColumns.includes(column.value);
        },

        toggleColumn(column) {
            if (this.isActive(column)) {
                this.activeHeaders = this.activeHeaders.filter(header => header.value != column.value)
            } else {
                this.activeHeaders.push(column)
            }
        },
        // toggleSelection(items) {
        //     console.log(itemsSelected);
        //     const index = this.itemsSelected.findIndex(itemsSelected=> itemsSelectedid === item.id);
        //     if (index !== -1) {
        //         this.itemsSelected.push(item);
        //     } else {
        //         this.itemsSelected.splice(index, 1);
        //     }
        // }
    },
});
