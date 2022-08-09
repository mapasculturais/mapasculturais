app.component('home-map', {
    template: $TEMPLATES['home-map'],
    
    created(){

        this.spaceAPI = new API('space');
        this.agentAPI = new API('agent');
        this.findEntities();
    },
    
    data() {
        return{
            agents: [],
            spaces: [],
        }
    },

    computed: {
        entities() {
            const entities = this.spaces.concat(this.agents);
            return entities;
        }
        // captar eventos
        // concatenar no foreach usando push
    },
    
    props: {
    },

    methods: {
        async findEntities() {
            const query = {
                '@select': 'id,name,shortDescription,location,terms,seals',
            }
            this.spaces = await this.spaceAPI.find(query);
            this.agents = await this.agentAPI.find(query);
        }
    },
});
