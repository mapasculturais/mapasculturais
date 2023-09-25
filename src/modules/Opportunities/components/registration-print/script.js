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
    
    data() {
        return {
            loading: false,
        }
    },
    
    methods: {
        print() {
            const self = this;
            self.loading = true;
            var iframe = this.$refs.printIframe;

            iframe.addEventListener("load", function(e) {      
                setTimeout(() => {
                    self.loading = false;
                }, 1000);
            });

            iframe.src = Utils.createUrl('registration', 'registrationPrint', [this.registration.id]);
        }        
    },
});
