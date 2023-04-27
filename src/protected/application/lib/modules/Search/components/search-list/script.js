app.component('search-list', {
    template: $TEMPLATES['search-list'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-list');

        return { text }
    },

    data() {
        return {
            query: {},
            typeText: '',
        }
    },

    created() {
        if (this.type == "agent") {
            this.typeText = __('text', 'search-list');
        }else {
            this.typeText = __('label', 'search-list');
        }
    },

    watch: {
        pseudoQuery: {
            handler(pseudoQuery) {
                this.query = Utils.parsePseudoQuery(pseudoQuery);
            },
            deep: true,
        }
    },

    props: {
        type: {
            type: String,
            required: true,
        },
        limit: {
            type: Number,
            default: 20,
        },
        select: {
            type: String,
            required: true
        },
        pseudoQuery: {
            type: Object,
            required: true
        }
    },

    computed: {
        entityType() {
            switch (this.type) {
                case 'agent':
                    return this.text('agente');
                case 'space':
                    return this.text('espaço');
                case 'event':
                    return this.text('evento');
                case 'opportunity':
                    return this.text('opportunidade');
                case 'project':
                    return this.text('projeto');
            }
        },
    },

    methods: {
        order(order) {
            let queryOrder = this.query['@order'];

            if (queryOrder.includes('ASC') && order == 'DESC') {
                this.query['@order'] = queryOrder.replace('ASC', order);
            } else if (queryOrder.includes('DESC') && order == 'ASC') {
                this.query['@order'] = queryOrder.replace('DESC', order);
            } else {
                this.query['@order'] = this.query['@order'] + ' ' + order;
            }
        }
    },
});
