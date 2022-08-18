app.component('search-list', {
    template: $TEMPLATES['search-list'],
    
    data() {
        return {
            statusLabel: '',
            typeLabel: '',
            termsLabel: '', 
        }
    },

    computed: {
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
            default: 'id,name,shortDescription,seals,terms,singleUrl'
        },
        api: {
            type: API,
            required: true
        }
    },

    methods: {

    },
});
