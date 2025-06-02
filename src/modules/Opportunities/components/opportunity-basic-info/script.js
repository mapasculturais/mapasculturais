app.component('opportunity-basic-info' , {
    template: $TEMPLATES['opportunity-basic-info'],

    setup() {
        const text = Utils.getTexts('opportunity-basic-info');
        return { text }
    },

    data () {
        return {
            phases: []
        };
    },

    async created() {
        if($MAPAS.opportunityPhases && $MAPAS.opportunityPhases.length > 0) {
            this.phases = $MAPAS.opportunityPhases;
        } else {
            const api = new OpportunitiesAPI();
            this.phases = await api.getPhases(this.entity.id);
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
});