/**
 * Vue Lifecycle
 * 1. setup
 * 2. beforeCreate
 * 3. created
 * 4. beforeMount
 * 5. mounted
 * 
 * // sempre que há modificação nos dados
 *  - beforeUpdate
 *  - updated
 * 
 * 6. beforeUnmount
 * 7. unmounted                  
 */

app.component('registration-print', {
    template: $TEMPLATES['registration-print'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {
        registration: {
            type: Entity,
            required: true,
        },
    },
    
    methods: {
        print() {
            var iframe = this.$refs.printIframe;
            iframe.src = Utils.createUrl('registration', 'registrationPrint', [this.registration.id]);
        }        
    },
});
