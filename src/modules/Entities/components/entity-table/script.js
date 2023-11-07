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

                case 10:
                    return 'row--selected';

                case 2:
                case 0:
                    return 'rew--invalid';

                // return 'pending';
                case 3:
                    return 'row--notapproved';
                case 8:
                    return 'row--waitlist';

                case null:
                default:
                    return '';
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
