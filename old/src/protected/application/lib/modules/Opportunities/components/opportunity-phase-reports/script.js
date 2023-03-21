app.component('opportunity-phase-reports', {
    template: $TEMPLATES['opportunity-phase-reports'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-phase-reports');
        return { text }
    },

    async created() {
        const api = new OpportunitiesAPI();

        this.phases = await api.getPhases(this.entity.id);
    },

    data() {
        return {
            phases: []
        }
    },

    computed: {
    },
    
    methods: {
    },
});
