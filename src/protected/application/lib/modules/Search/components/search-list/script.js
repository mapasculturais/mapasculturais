app.component('search-list', {
    template: $TEMPLATES['search-list'],
    
    data() {
        return {
            query: {}
        }
    },

    watch: {
        pseudoQuery: {
            handler(pseudoQuery){
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
            default: 'id,name,type,shortDescription,files.avatar,seals,terms,singleUrl'
        },
        pseudoQuery: {
            type: Object,
            required: true
        }
    },

    methods: {

    },
});
