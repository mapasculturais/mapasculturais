app.component('registration-valuers-list', {
    template: $TEMPLATES['registration-valuers-list'],

    props: {
        registration_id: {
            type: [String, Number],
            default: null,
        }
    },
  
    data() {
        return {
            valuersExceptionsList:{}
        }
    },

    computed: {
        committee() {
            return $MAPAS.config.registrationValuersList
        },
        registration() {
            const api = new API('registration');
            const registration = api.getEntityInstance(this.registration_id);
            return registration;
        },
        opportunity() {
            return this.registration.opportunity;
        },
        exceptionsList() {
            return this.committee.valuersExceptionsList
        },
    },
    
    methods: { },
});
