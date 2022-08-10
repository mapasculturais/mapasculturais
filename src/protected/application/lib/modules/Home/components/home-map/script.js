app.component('home-map', {
    template: $TEMPLATES['home-map'],
    
    async created(){

        this.spaceAPI = new API('space');
        this.agentAPI = new API('agent');
        
        const query = {
            '@select': 'id,name,location',
            '@verified': 1
        }
        this.spaces = await this.spaceAPI.find(query);
        this.agents = await this.agentAPI.find(query);
    },
    
    data() {
        return {
            agents: Vue.shallowReactive([]),
            spaces: Vue.shallowReactive([]),
        }
    },

    computed: {
        entities() {
            const entities = this.spaces.concat(this.agents);
             
            return Vue.shallowReactive(entities);
        }
        // captar eventos
        // concatenar no foreach usando push
    },
    
    props: {
    },

    methods: {
    },
});
