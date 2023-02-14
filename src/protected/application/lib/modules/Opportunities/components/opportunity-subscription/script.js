app.component('opportunity-subscription' , {
    template: $TEMPLATES['opportunity-subscription'],

    data () {
        return {
            agent: null,
            categories: $MAPAS.requestedEntity.registrationCategories,
            dateStart: new McDate($MAPAS.requestedEntity.registrationFrom.date),
            dateEnd: new McDate($MAPAS.requestedEntity.registrationTo.date),
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    computed: {
        isLogged() {
            return $MAPAS.userId != null
        },
        startAt() {
            return this.dateStart.day('numeric') + '/' + this.dateStart.month('numeric') + '/' + this.dateStart.year('numeric');
        },
        endAt() {
            return this.dateEnd.day('numeric') + '/' + this.dateEnd.month('numeric') + '/' + this.dateEnd.year('numeric');
        },
        hourEnd() {
            return this.dateEnd.time();
        }
    },

    methods: {
        // Seleção do espaço vinculado
        selectAgent(agent) {
            this.agent = agent;
        },
        removeAgent() {
            this.agent = null;
        },
    }
});