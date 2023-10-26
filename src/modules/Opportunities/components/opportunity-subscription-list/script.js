app.component('opportunity-subscription-list' , {
    template: $TEMPLATES['opportunity-subscription-list'],

    setup() {
        const api = new API('registration');
        const list = [];
        for (let raw of $MAPAS.config.opportunitySubscriptionList.registrations) {
            const registration = api.getEntityInstance(raw.id);
            registration.populate(raw);
            registration.$LISTS.push(list);
            list.push(registration);
        }
        $MAPAS.userRegistrations = list;
    },

    data() {
        const registrations = $MAPAS.userRegistrations;
        
        return {
            registrations,
            opportunity: $MAPAS.requestedEntity,
        }
    },

    computed: {
        registrationStatus() {
            let _actualDate = new Date();
            let _fromDate = new McDate(this.opportunity.registrationFrom?.date)._date;
            let _toDate = new McDate(this.opportunity.registrationTo?.date)._date;

            if (_fromDate < _actualDate && _toDate > _actualDate) {
                return 'open';
            }

            if (_toDate < _actualDate) {
                return 'closed';
            }

            return false;
        },
        registrationsOpen() {
            for (const registration of this.registrations) {
                if (registration.status == 0) {
                    return true;
                }
            }
            return false;
        }
    }
});