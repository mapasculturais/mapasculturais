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

app.component('mc-debug', {
    template: $TEMPLATES['mc-debug'],

    props: {
        type: {
            type: String,
            required: true
        },

        name: {
            type: String,
            required: true
        }
    }
});
