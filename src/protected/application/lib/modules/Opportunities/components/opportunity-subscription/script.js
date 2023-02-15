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
            categories: this.entity.registrationCategories,
            dateStart: this.entity.registrationFrom, 
            dateEnd: this.entity.registrationTo,
            entities: {},
            entitiesLength: $MAPAS.config.opportunitySubscription.agents.length,
        }
    },

    computed: {
        isLogged() {
            return $MAPAS.userId != null
        },

        infoRegistration() {
            let description = ''

            let registrationStatus = this.registrationStatus(this.dateStart, this.dateEnd);

            switch (registrationStatus) {
                case 'open':
                    description = 'Inscrições abertas de {startAt} a {endAt}  às {endHour}';
                    break;
                case 'closed':
                    description = 'As inscrições estão encerradas'
                    break;
                case 'will open':
                    description = 'As inscrições ainda não estão abertas. O período de inscrições começará a partir do dia {startAt} às {startHour}'
                    break;
            }

            description = description.replace("{startAt}", this.startAt);
            description = description.replace("{startHour}", this.startHour);
            description = description.replace("{endAt}", this.endAt);
            description = description.replace("{endHour}", this.endHour);

            return description;
        },

        startAt() {
            return this.dateStart.day('2-digit') + '/' + this.dateStart.month('2-digit') + '/' + this.dateStart.year('numeric');
        },
        endAt() {
            return this.dateEnd.day('2-digit') + '/' + this.dateEnd.month('2-digit') + '/' + this.dateEnd.year('numeric');
        },
        startHour() {
            return this.dateStart.time();
        },
        endHour() {
            return this.dateEnd.time();
        },
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
        },
        registrationStatus(dateStart, dateEnd) {
            let _actualDate = new Date();

            let _dateStart = dateStart.year('numeric') + '/' + dateStart.month('2-digit') + '/' + dateStart.day('2-digit') + ', ' + dateStart.hour('2-digit') + ':' + dateStart.minute('2-digit') + ':' + dateStart.second('2-digit');
            _dateStart = new Date(_dateStart);

            let _dateEnd = dateEnd.year('numeric') + '/' + dateEnd.month('2-digit') + '/' + dateEnd.day('2-digit') + ', ' + dateEnd.hour('2-digit') + ':' + dateEnd.minute('2-digit') + ':' + dateEnd.second('2-digit');
            _dateEnd = new Date(_dateEnd);

            if (_dateStart > _actualDate) {
                return 'will open';
            } else {
                if (_dateEnd > _actualDate) {
                    return 'open';
                } else {
                    return 'closed';
                }
            }     
        },
    }
});