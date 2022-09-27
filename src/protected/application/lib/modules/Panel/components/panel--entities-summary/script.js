app.component('panel--entities-summary', {
    template: $TEMPLATES['panel--entities-summary'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('panel--entities-summary')
        return { text }
    },

    beforeCreate() { },
    created() { },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    props: {

    },

    data() {
        console.log($MAPAS.config);
        return {
            spaces: $MAPAS.config.entitySummary.spaces,
            agents: $MAPAS.config.entitySummary.agents,
            events: $MAPAS.config.entitySummary.events,
            projects: $MAPAS.config.entitySummary.projects,
            opportunities: $MAPAS.config.entitySummary.opportunities,
        }
    },

    computed: {
    },
    
    methods: {
    },
});
