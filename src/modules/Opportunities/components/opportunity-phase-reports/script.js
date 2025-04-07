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
        newPhases () {

            const newPhases = [];

            this.phases.forEach((phase, index) => {
                const previousPhase = this.phases[index-1];
                if(phase.__objectType === 'evaluationmethodconfiguration' && phase.opportunity.id === previousPhase.id) {
                    const phases = newPhases.flatMap(item => item.phases);
                    const indexPhaseDeleted = phases.indexOf(previousPhase);
                    newPhases.splice(indexPhaseDeleted, 1);

                    newPhases.push({
                        label: `${this.text('periodo_inscricao')} / ${phase.name}`,
                        id: previousPhase.id
                    });
                } else {
                    newPhases.push({
                        type: this.evaluationMethods[phase.type] ? this.evaluationMethods[phase.type].name : '',
                        label: phase.name,
                        id: phase.__objectType === 'evaluationmethodconfiguration' ? phase.opportunity.id : phase.id
                    });
                }

            });

            return newPhases;
        }
    },
});
