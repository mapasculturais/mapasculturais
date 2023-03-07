app.component('search-list', {
    template: $TEMPLATES['search-list'],

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
            default: 'id,name,type,shortDescription,files.avatar,seals,endereco,acessibility,terms,singleUrl'
        },
        pseudoQuery: {
            type: Object,
            required: true
        }
    },

    methods: {

    },
});
