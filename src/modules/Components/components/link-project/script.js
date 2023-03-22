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

app.component('link-project', {
    template: $TEMPLATES['link-project'],
    
    // define os eventos que este componente emite
    emits: [''],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('link-project')
        return { text }
    },

  

    props: {
        entity: {
            type: Entity,
            required: true
        },

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
