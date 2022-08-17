app.component('home-map', {
    template: $TEMPLATES['home-map'],
    
    async created(){
        const spaceAPI = new API('space');
        const agentAPI = new API('agent');

        const query = this.query;
        
        query['@select'] = 'id,type,name,location,singleUrl';
        query['@order'] = this.order;
        query['location'] = '!EQ([0,0])';

        if(this.limit) {
            query['@limit'] = this.limit;
        }
        console.time('home-map: fetchEntities');
        this.spaces = await spaceAPI.find(query, null, true);
        this.agents = await agentAPI.find(query, null, true);
        console.timeEnd('home-map: fetchEntities');

    },
    
    data() {
        return {
            agents: [],
            spaces: [],
        }
    },

    computed: {
        entities() {
            let entities = [];

            if (this.spaces instanceof Array) {
                entities = entities.concat(this.spaces);
            } 
            
            if (this.agents instanceof Array) {
                entities = entities.concat(this.agents);
            } 
            return Vue.shallowReactive(entities);
        }
    },
    
    props: {
        limit: {
            type: Number
        },
        order: {
            type: String,
            default: 'createTimestamp DESC'
        },
        query: {
            type: Object,
            default: {}
        },
        text: String
    },

    methods: {
    },
});
