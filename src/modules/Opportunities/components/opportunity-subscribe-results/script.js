app.component('opportunity-subscribe-results', {
    template: $TEMPLATES['opportunity-subscribe-results'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        tab: {
            type: String,
        },
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-subscribe-results');
        return { text }
    },

    async created() {
        if($MAPAS.opportunityPhases && $MAPAS.opportunityPhases.length > 0) {
            this.phases = $MAPAS.opportunityPhases;
        } else {
            const api = new OpportunitiesAPI();
            this.phases = await api.getPhases(this.entity.id);
        }
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
        showPublishTimestamp(phase) {
            const previousPhase = this.getPreviousPhase(phase);
            const nextPhase = this.getNextPhase(phase);

            if (phase.isLastPhase) {
                return true;
            } else if (phase.__objectType == 'opportunity' && nextPhase.__objectType != 'evaluationmethodconfiguration' && phase.publishTimestamp) {
                return true;
            } else if (phase.__objectType == 'evaluationmethodconfiguration' && previousPhase.__objectType == 'opportunity' && previousPhase.publishTimestamp) {
                return true;
            } else {
                return false;
            }
        },
        publishTimestamp(phase) {
            if (phase.__objectType == 'opportunity') {
                return phase.publishTimestamp;
            } 
            
            if (phase.__objectType == 'evaluationmethodconfiguration') {
                return phase.opportunity.publishTimestamp;
            }
        },
        getPreviousPhase(phase) {
            const index = this.phases.indexOf(phase);
            return this.phases[index - 1];
        },

        getNextPhase(phase) {
            const index = this.phases.indexOf(phase);
            return this.phases[index + 1];
        },
    },
});
