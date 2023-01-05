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

app.component('seal-activity-card', {
    template: $TEMPLATES['seal-activity-card'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('seal-activity-card')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: false
        }


    },

    data() {
        return {
            
        }
    },

    computed: {
      
    },
    
    methods: {
        
    },
});
