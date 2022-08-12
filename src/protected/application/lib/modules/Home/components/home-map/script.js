app.component('home-map', {
    template: $TEMPLATES['home-map'],
    
    async created(){

        this.spaceAPI = new API('space');
        this.agentAPI = new API('agent');
        
        const query = this.query;
        query['@select'] = 'id,name,location';

        if(this.limit) {
            query['@limit'] = this.limit;
        }

        query['@order'] = this.order;
            
        this.spaces = await this.spaceAPI.find(query);
        this.agents = await this.agentAPI.find(query);
    },
    
    data() {
        return {
            agents: [],
            spaces: [],
        }
    },

    computed: {
        entities() {
            const entities = this.spaces.concat(this.agents);
            return Vue.shallowReactive(entities);
        }
    },
    
    props: {
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
