app.component('opportunity-subscription-list' , {
    template: $TEMPLATES['opportunity-subscription-list'],

    setup() {
        $MAPAS.config.opportunitySubscriptionList.registrations = $MAPAS.config.opportunitySubscriptionList.registrations.map((registration) => {
            if (registration instanceof Entity) {
                return registration;
            } else {
                const entity = new Entity('registration', registration.id);
                entity.populate(registration);
                return entity;
            }             
        });
    },

    data() {
        return {
            registrations: $MAPAS.config.opportunitySubscriptionList.registrations,
        }
    },

    computed: {
        isLogged() {
            return $MAPAS.userId != null
        }
    }
});