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


app.component('notifications-list', {
    template: $TEMPLATES['notifications-list'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('notifications-list')
        return { text }
    },

    beforeCreate() { },
    created() { },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    props: {
        query:{
            type:Object,
            default:{
                '@select':'*',
                'user':'eq(@me)',
                '@order':'createTimestamp DESC'
            }
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
