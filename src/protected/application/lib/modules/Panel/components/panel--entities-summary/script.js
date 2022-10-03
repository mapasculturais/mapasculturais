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
        return {
            spaces: {
                title: $MAPAS.config.entitySummary.spaces > 1 ? __('espaços', 'panel--entities-summary') : __('espaço', 'panel--entities-summary'),
                count: $MAPAS.config.entitySummary.spaces,
            },
            agents: {
                title: $MAPAS.config.entitySummary.agents > 1 ? __('agentes', 'panel--entities-summary') : __('agente', 'panel--entities-summary'),
                count: $MAPAS.config.entitySummary.agents,
            },
            events: {
                title: $MAPAS.config.entitySummary.events > 1 ? __('eventos', 'panel--entities-summary') : __('evento', 'panel--entities-summary'),
                count: $MAPAS.config.entitySummary.events,
            },
            projects: {
                title: $MAPAS.config.entitySummary.projects > 1 ? __('projetos', 'panel--entities-summary') : __('projeto', 'panel--entities-summary'),
                count: $MAPAS.config.entitySummary.projects,
            },
            opportunities: {
                title: $MAPAS.config.entitySummary.opportunities > 1 ? __('oportunidades', 'panel--entities-summary') : __('oportunidade', 'panel--entities-summary'),
                count: $MAPAS.config.entitySummary.opportunities,
            },
        }
    },

    computed: {
    },
    
    methods: {
    },
});
