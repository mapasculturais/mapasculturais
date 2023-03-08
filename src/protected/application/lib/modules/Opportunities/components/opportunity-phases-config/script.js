app.component('opportunity-phases-config', {
    template: $TEMPLATES['opportunity-phases-config'],
    
    // define os eventos que este componente emite
    emits: ['newPhase', 'newDataCollectionPhase', 'newEvaluationPhase'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-phases-config')
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
        categories(){
            return this.entity.registrationCategories instanceof Array ?  this.entity.registrationCategories : [];
        }
    },
    
    methods: {
        addInPhases (phase) {
            this.phases.splice(this.phases.length - 1, 0, phase);
        }
    },
});
