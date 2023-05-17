app.component('opportunity-phase-evaluation', {
    template: $TEMPLATES['opportunity-phase-evaluation'],
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-phase-evaluation')
        return { text, hasSlot }
    },
    data() {
        const api = new API("evaluationmethodconfiguration");

        let evaluationTypes = $DESCRIPTIONS.evaluationmethodconfiguration.type.options;
        let phases = [];
        listPhases = $MAPAS.evaluationPhases?.map(function(item){
            let eval = api.getEntityInstance(item.id);
            return eval
        });

        return {
            evaluationTypes,
            phases: listPhases || []
        }
    },
});
