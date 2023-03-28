app.component('opportunity-subscribe-results', {
    template: $TEMPLATES['opportunity-subscribe-results'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-subscribe-results');
        return { text }
    },

    async created() {
        const api = new OpportunitiesAPI();
        
        this.phases = await api.getPhases(this.entity.id);
    },

    data() {
        return {
            phases: [],
            evaluationMethods: $MAPAS.config.evaluationMethods
        }
    },

    computed: {
    },
    
    methods: {
    },
});
