app.component('entity-table', {
    template: $TEMPLATES['entity-table'],

    setup({ headers }) {
        const activeHeaders = Vue.ref(headers.filter(
            header => header.required
        ));
        return { activeHeaders };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        headers: {
            type: Array,
            required: true
        },
        items: {
            type: Array,
            required: true
        },


        classes: {
            type: [String, Array, Object],
            required: false
        },

    },
    computed: {
        activeColumns() {
            return this.activeHeaders.map(header => (header.value));
        },
    },

    methods: {
        customRowClassName(row) {
            switch (row.status) {
                case 0:
                    return 'draft'
                case 1:
                    return 'sent'
                case 10:
                    return 'entity-table__row--selected'
                default:
                    break;
            }
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
    },
});