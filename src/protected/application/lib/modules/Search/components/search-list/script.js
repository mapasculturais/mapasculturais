app.component('search-list', {
    template: $TEMPLATES['search-list'],
    
    async created(){

        this.entityAPI = new API(this.type);
        
        const query = this.query;
        query['@select'] = 'id,name,location';

        if(this.limit) 
            query['@limit'] = this.limit;
        
        query['@order'] = this.order;
        this.response = await this.entityAPI.find(query);
    },
    
    data() {
        return {
            response: [],
        }
    },

    computed: {
        entities() {
            return Vue.shallowReactive(this.response);
        }
    },
    
    props: {
        type: {
            type: String,
            required: true,
        },
        limit: {
            type: Number,
            default: null
        },
        order: {
            type: String,
            default: 'createTimestamp DESC'
        },
        query: {
            type: Object,
            default: {}
        }
    },

    methods: {
    },
});
