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

app.component('mc-header-menu', {
    template: $TEMPLATES['mc-header-menu'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-header-menu')
        return { text }
    },

    props: {
    },

    data() {
        return {
            openMobile: false,
        }
    },

    computed: {
    },
    
    methods: {
        toggleMobile() {
            this.openMobile = !this.openMobile;
        }
    },
});
