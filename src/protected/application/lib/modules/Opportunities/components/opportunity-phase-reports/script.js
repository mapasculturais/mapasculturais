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
            phases: [],
            evaluationMethods: $MAPAS.config.evaluationMethods
        }
    },

    computed: {
    },
    
    methods: {
        isJoinedPhaseLabel (index) {
            const currentPhase = this.phases[index];
            const previousPhase = this.phases[index - 1];

            if(currentPhase.__objectType === 'evaluationmethodconfiguration' && currentPhase.opportunity.id === previousPhase.id) {
                return `${this.text('periodo_inscricao')} - ${currentPhase.name}`;
            }
            return currentPhase.name;
        },
        isJoinedPhase (index) {
            const currentPhase = this.phases[index];
            const previousPhase = this.phases[index - 1];

            return currentPhase.__objectType === 'evaluationmethodconfiguration' && currentPhase.opportunity.id === previousPhase.id;

        }
    },
});
