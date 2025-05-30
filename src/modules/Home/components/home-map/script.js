app.component('home-map', {
    template: $TEMPLATES['home-map'],

    setup() {
        const global = useGlobalState();
        return { global }
    },
    
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
        const requests = [];
        requests.push(this.hideSpaces ? [] : spaceAPI.find(query, null, true));
        requests.push(this.hideAgents ? [] : agentAPI.find(query, null, true));

        Promise.all(requests).then((responses) => {
            this.spaces = responses[0];
            this.agents = responses[1];
        }).finally(() => {
            console.timeEnd('home-map: fetchEntities');
        }).catch((err) => {
            console.error(err);
        });
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

            if (this.spaces instanceof Array && this.global.enabledEntities.spaces && !this.hideSpaces) {
                entities = entities.concat(this.spaces);
            } 
            
            if (this.agents instanceof Array && this.global.enabledEntities.agents && !this.hideAgents) {
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
        text: String,
        hideAgents: {
            type: Boolean,
            default: false
        },
        hideSpaces: {
            type: Boolean,
            default: false
        },
    },

    methods: {
    },
});
