app.component('opportunity-basic-info' , {
    template: $TEMPLATES['opportunity-basic-info'],

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


    computed: {
        lastPhase () {
            const phase = this.phases.find(item => item.isLastPhase);
            return phase;
        }
    },

    watch: {
        'entity.isContinuousFlow'(newVal) {
            if (!newVal) {
                this.entity.hasEndDate = false;
                this.lastPhase.name = "Publicação final do resultado";
                this.lastPhase.save();
            } else {
                this.lastPhase.name = "Resultado";
                this.lastPhase.save();
            }
        },

        'entity.hasEndDate'(newVal) {
            if (!newVal) {
                const myDate = new McDate(new Date(`2111-01-01 00:00`));
                
                this.entity.continuousFlow = myDate.sql('full');
                this.entity.registrationTo = myDate.sql('full');
            } else {
                this.entity.continuousFlow = null;
                this.entity.registrationTo = null;
            }
        },
    }
});