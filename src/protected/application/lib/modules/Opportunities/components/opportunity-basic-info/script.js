app.component('opportunity-basic-info' , {
    template: $TEMPLATES['opportunity-basic-info'],

    data () {
        return {
            phases: []
        };
    },

    async created() {
        const api = new OpportunitiesAPI();

        this.phases = await api.getPhases(this.entity.id);

        console.log('this.phases');
        console.log(this.phases);
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },


    computed: {
        lastPhase () {
            const phase = this.phases.find(item => item.isLastPhase);
            return phase;
        }
    }
});