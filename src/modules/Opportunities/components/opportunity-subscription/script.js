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

        const text = Utils.getTexts('opportunity-subscription')
        return { text }
    },

    data () {
        let agent = null;
        let phases = null;

        if ($MAPAS.config.opportunitySubscription.agents.length == 1) {
            agent = $MAPAS.config.opportunitySubscription.agents[0];
        }

        if($MAPAS.opportunityPhases && $MAPAS.opportunityPhases.length > 0) {
            phases = $MAPAS.opportunityPhases;
        } 

        return {
            agent,
            category: null,
            registrationRange: null,
            registrationProponentType: null,
            categories: this.entity.registrationCategories || [],
            dateStart: this.entity.registrationFrom, 
            dateEnd: this.entity.registrationTo,
            entities: {},
            entitiesLength: $MAPAS.config.opportunitySubscription.agents.length,
            processing: false,
            phases,
            totalRegistrations: $MAPAS.config.opportunitySubscription.totalRegistrations,
            totalRegistrationsPerUser: $MAPAS.config.opportunitySubscription.totalRegistrationsPerUser,
            registrationProponentTypes: this.entity.registrationProponentTypes || [],
            registrationRanges: this.entity.registrationRanges || [],
        }
    },

    computed: {
        numberFields() {
            return [
                this.entitiesLength > 1, 
                this.categories.length, 
                this.registrationRanges.length, 
                this.registrationProponentTypes.length
            ].filter(i => i).length;
        },
        infoRegistration() {
            let description = ''

            let registrationStatus = this.registrationStatus(this.dateStart, this.dateEnd);

            if (this.isPublished) {
                description = this.text('resultado publicado');
            } else if (!this.dateStart) {
                description = this.text('inscrições indefinidas');
            } else if (this.registrationLimit) {
                description = this.text('limite de inscrições');
            } else if (this.registrationLimitPerOwner) {
                description = this.text('limite de inscrições por usuário');
            } else {
                switch (registrationStatus) {
                    case 'open':
                        description = this.text('inscrições abertas');
                        break;
                    case 'closed':
                        description = this.text('inscrições fechadas');
                        break;
                    case 'will open':
                        description = this.text('inscrições irão abrir');
                        break;
                }
            }

            description = description.replace("{startAt}", this.startAt);
            description = description.replace("{startHour}", this.startHour);
            description = description.replace("{endAt}", this.endAt);
            description = description.replace("{endHour}", this.endHour);

            return description;
        },

        isOpen() {
            if (this.entity.currentUserPermissions["@control"]){
                return true;
            }
            
            if (this.registrationStatus(this.dateStart, this.dateEnd) == 'open') {
                return true;
            } else {
                return false;
            }
        },
        isPublished() {
            let _actualDate = new Date();

            if (this.lastPhase.publishTimestamp?._date < _actualDate) {
                return true;
            }
            return false;
        },
        registrationLimit() {
            if (this.entity.registrationLimit) {
                return this.totalRegistrations >= this.entity.registrationLimit;
            }
            return false;
        },
        registrationLimitPerOwner() {
            if (this.entity.registrationLimitPerOwner) {
                return this.totalRegistrationsPerUser >= this.entity.registrationLimitPerOwner;
            }
            return false;
        },
        startAt() {
            return this.dateStart?.date('2-digit year');
        },
        endAt() {
            return this.dateEnd?.date('2-digit year');
        },
        startHour() {
            return this.dateStart?.time();
        },
        endHour() {
            return this.dateEnd?.time();
        },
        lastPhase () {
            const phase = this.phases.find(item => item.isLastPhase);
            return phase;
        },
    },

    methods: {
        selectAgent(agent) {
            this.agent = agent;
        },
        removeAgent() {
            this.agent = null;
        },
        redirectLogin() {
            let url = Utils.createUrl('auth', '') + `?redirectTo=${this.entity.singleUrl.pathname}`;
            window.location.href = url;
        },
        fetch(entities) {
            this.entities = entities;

            if (this.entities.length == 1) {
                this.selectAgent(this.entities[0]);
            }
        },
        registrationStatus(dateStart, dateEnd) {
            let _actualDate = new Date();

            if (dateStart?._date > _actualDate) {
                return 'will open';
            } else {
                if (dateEnd?._date > _actualDate) {
                    return 'open';
                } else {
                    return 'closed';
                }
            }     
        },
        async subscribe() {
            const messages = useMessages();

            if (!this.agent) {
                messages.error(this.text('selecione agente'));
                return;
            } else if (this.categories?.length && !this.category) {
                messages.error(this.text('selecione categoria'));
                return;
            } else if (this.registrationRanges?.length && !this.registrationRange) {
                messages.error(this.text('selecione faixa'));
                return;
            } else if (this.registrationProponentTypes?.length && !this.registrationProponentType) {
                messages.error(this.text('selecione tipo do preponente'));
                return;
            }
            this.processing = true;

            const registration = new Entity('registration');
            registration.opportunity = this.entity;
            registration.owner = this.agent;
            if (this.category) {
                registration.category = this.category;
            }
            if (this.registrationRange) {
                registration.range = this.registrationRange;
            }
            if (this.registrationProponentType) {
                registration.proponentType = this.registrationProponentType;
            }

            registration.disableMessages();
            try {
                await registration.save().then(res => {
                    window.location.href = registration.editUrl;
                });    
            } catch (error) {
                if (error.error) {
                    for (let key in error.data) {
                        if (error.data[key] instanceof Array) {
                            for (let val of error.data[key]) {
                                messages.error(val);
                            }
                        }
                        if (!(error.data[key] instanceof Array)) {
                            for (let _key in error.data[key]) {
                                if (error.data[key][_key] instanceof Array) {
                                    for (let _val of error.data[key][_key]) {
                                        messages.error(_val);
                                    }
                                } else {
                                    messages.error(error.data[key][_key]);
                                }
                            }
                        }
                    }
                    this.processing = false;
                }
            }
        }
    }
});