app.component('search-list', {
    template: $TEMPLATES['search-list'],

    data() {
        
        return {
            query: {},
            typeText: '',
        }
    },

    created()
    {
        
        switch (this.type) {
            case "agent":
                this.typeText = "Este agente é definido como: ";

                break;
            case 'event':
                this.typeText = "Tipo: ";

                break;
            case 'space':
                this.typeText = "Tipo: ";
                break;
            case 'project':
                this.typeText = "Tipo: ";
                break;
            case 'opportunity':
                this.typeText = "Tipo: ";
                break;

            default:
                break;
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
