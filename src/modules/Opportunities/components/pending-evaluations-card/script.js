app.component('pending-evaluations-card', {
    template: $TEMPLATES['pending-evaluations-card'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('pending-evaluations-card');
        const entities = [];
        const api = new API("opportunity");
        
        
        for(let raw of $MAPAS.opportunitiesCanBeEvaluated){
           const opportunity = api.getEntityInstance(raw.id);
           opportunity.populate(raw);
           entities.push(opportunity);
        }
        
        return { text, entities }
    },

    created() {
        this.loading = false;
    },
 
    data() {
        return {
            loading: true,

            phases: [],
            evaluationMethods: $MAPAS.config.evaluationMethods,
            evaluationTypes: $DESCRIPTIONS.evaluationmethodconfiguration.type.options
        }
    },
    methods: {
        evaluationFrom(entity) {
            const evalFrom = entity.evaluationMethodConfiguration.evaluationFrom;
            return evalFrom
        },

        evaluationTo(entity) {
            const evalTo = entity.evaluationMethodConfiguration.evaluationTo;
            return  evalTo
        },

        ownerType(owner) {
            switch (owner.__objectType) {
                case 'agent':
                    return this.text('agente');
                case 'space':
                    return this.text('espaço');
                case 'event':
                    return this.text('evento');
                case 'opportunity':
                    return this.text('opportunidade');
                case 'project':
                    return this.text('projeto');
            }
        }
    },

});
