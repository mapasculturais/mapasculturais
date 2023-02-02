app.component('opportunity-timeline-phases' , {
    template: $TEMPLATES['opportunity-timeline-phases'],

    data () {
        return {
            phases: []
        }
    },

    computed: {
        isLogged () {
            return $MAPAS.userId != null
        },
        id () {
            return $MAPAS.requestedEntity.id
        }
    },

    async created () {
        this.API = new API('opportunity');
        this.phases = await this.API.GET(`/api/opportunity/phases?@opportunity=${this.id}`);
        console.log(this.phases);
    }
});