app.component('entity-table', {
    template: $TEMPLATES['entity-table'],

    setup({ headers }, { slots }) {
        const activeSlots = Object.keys(slots)
        const hasSlot = name => !!slots[name];
        const activeHeaders = Vue.ref(headers.filter(
            header => header.required
        ));
        return { activeHeaders, hasSlot, activeSlots };
    },
    data() {
        return {
            itemsSelected: Vue.ref([]),

        }
    },
    // Consulta em qualquer API
    // Passar a rota do endpoint?
    // find - row processer? pega o retorno e transforma num entity.
    // No caso da listagem de avaliação não poderá ser um entity (Exceção)
    // passar pro componente 
    // Inscrições e todos os outros usos pode
    // se nao der certo usando entities, deve-se usar uma query ..via ajax.
    // carregar mais ao invés da paginaçao na tabela.
    props: {
        headers: {
            type: Array,
            required: true
        },
        items: {
            type: Array,
            required: true
        },

        // statusClasses: {
        //     type: Object,
        //     default: {
        //         '-10': 'row--trash',
        //         '-2': 'row--archived',
        //         '-9': 'row--disabled',
        //         '0': 'row--draft',
        //         '1': 'row--enabled row--sent',
        //         '2': 'row--invalid',
        //         '3': 'row--notapproved',
        //         '8': 'row--waitlist',
        //         '10': 'row--approved',
        //     }
        // },

    },
    computed: {
        activeColumns() {
            return this.activeHeaders.map(header => (header.value));
        },
    },

    methods: {
        // customRowClassName() {
        //     return this.statusClasses[items.status];
        // },
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
