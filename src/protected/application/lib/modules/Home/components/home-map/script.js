app.component('home-map', {
    template: $TEMPLATES['home-map'],
    
    data() {
        return{
            select: 'id,name,shortDescription,terms,seals',
            query: {"@permissions": ""}
        }
    },

    computed: {
        async allEntities() {
            let entities = {};
            let newIndex = 0;
            const spaceAPI = new API('space');
            const agentAPI = new API('agent');
            const spaces = await spaceAPI.find();
            const agents = await agentAPI.find();

            spaces.forEach(function(entity, index){
                entities[newIndex] = entity;
                ++newIndex;
            });

            agents.forEach(function(entity, index){
                entities[newIndex] = entity;
                ++newIndex;
            });

            // entities.concat(spaces).concat(agents);
            console.log(entities);
            return entities;
        }
        // captar eventos
        // concatenar no foreach usando push


    },
    







    props: {
    },

    methods: {
        
    },
});
