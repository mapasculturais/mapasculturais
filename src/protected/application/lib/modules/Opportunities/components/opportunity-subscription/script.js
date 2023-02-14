app.component('opportunity-subscription' , {
    template: $TEMPLATES['opportunity-subscription'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    setup() {
        $MAPAS.config.opportunitySubscription.agents = $MAPAS.config.opportunitySubscription.agents.map((agent) => {
            if (agent instanceof Entity) {
                return agent;
            } else {
                const entity = new Entity('agent', agent.id);
                entity.populate(agent);
                return entity;
            }             
        });
    },

    data () {
        let agent = null;

        if ($MAPAS.config.opportunitySubscription.agents.length == 1) {
            agent = $MAPAS.config.opportunitySubscription.agents[0];
        }

        return {
            agent,
            categories: $MAPAS.requestedEntity.registrationCategories,
            dateStart: new McDate($MAPAS.requestedEntity.registrationFrom.date),
            dateEnd: new McDate($MAPAS.requestedEntity.registrationTo.date),
            entities: {},
            entitiesLength: $MAPAS.config.opportunitySubscription.agents.length,
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
        selectAgent(agent) {
            this.agent = agent;
        },
        removeAgent() {
            this.agent = null;
        },
        fetch(entities) {
            this.entities = entities;

            if (this.entities.length == 1) {
                this.selectAgent(this.entities[0]);
            }
        }
    }
});