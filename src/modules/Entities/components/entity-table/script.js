app.component('entity-table', {
    template: $TEMPLATES['entity-table'],

    setup({ headers }, { slots }) {
        const activeSlots = Object.keys(slots)
        const hasSlot = name => !!slots[name];

        return { optionalHeadersSelected: [], hasSlot, activeSlots };
    },

    created() {
        

    },
    mounted() {
        this.modifiedHeaders.forEach( header =>{
            if(header.visible || header.required) {
                this.addInColumns(header.value);
            }
        });
    },
    data() {
        const visible = this.visible.split(",");
        const required = this.required.split(",");
        const modifiedHeaders = this.headers.map(header => {
            if (visible.includes(header.value)) {
                return { ...header, visible: true };
            }
            if (required.includes(header.value)) {
                return { ...header, required: true };
            }
            return header;
        });
        const activeHeaders = Vue.ref(modifiedHeaders.filter(
            header => header.required
        ));
        return {
            itemsSelected: Vue.ref([]),
            modifiedHeaders,
            activeHeaders,
        }

    },
    

    // O componente deve receber uma listagem de colunas existentes

    // Listagem de colunas opcionais.

    // Listagem de colunas selecionadas por padrão.

    props: {
        headers: {
            type: Array,
            required: true
        },
        required: {
            type: String,
            default: ''
        },
        // optionalColumns: {
        //     type: String,
        //     default: ''
        // },
        labelColumn: {
            type: String,
            default: 'nome'
        },
        visible: {
            type: String,
            default: ''
        },
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
        selectRows() {
            return this.optionalHeaders.map(header => {
                return {
                    label: header.text,
                    value: header.value,
                }
            })
        },

        activeColumns() {
            return this.activeHeaders.map(header => (header.value));
        },

        visibleColumns() {
            return this.activeHeaders.reduce((columns, header) => {
                if (header.visible) {
                    columns.push(header);
                }
                return columns;
            }, []);
        },
        selectedColumns() {
            return this.activeHeaders.reduce((columns, header) => {
                // if (!header.required) {
                    columns.push(header.text);
                // }
                return columns;
            }, []);
        },

        optionalHeaders() {
            return this.modifiedHeaders.reduce((columns, header) => {
                if (!header.required) {
                    columns.push(header);
                }
                return columns;
            }, []);
            // return this.modifiedHeaders.filter(header => !header.required);
        },
    },

    methods: {
        removeFromColumns(tag) {
            if (this.activeColumns.includes(tag)) {
                this.activeHeaders = this.activeHeaders.filter(header => (header.value != tag || header.required));
                console.log(this.activeHeaders);
            }
        },
        addInColumns(tag) {
            // console.log(this.optionalHeaders.includes(tag));
            // console.log(this.optionalHeaders.find(header =>header.value == tag));
            if (!this.activeColumns.includes(tag)) {
                this.activeHeaders.push(this.optionalHeaders.find(header => header.value == tag));
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
