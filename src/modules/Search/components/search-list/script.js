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
            order: "createTimestamp DESC",
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

    mounted() {
        this.query = Utils.parsePseudoQuery(this.pseudoQuery);
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
});
