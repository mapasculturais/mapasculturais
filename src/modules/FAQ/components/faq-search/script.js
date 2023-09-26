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

app.component('faq-search', {
    template: $TEMPLATES['faq-search'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {

    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('faq-search')
        return { text, hasSlot }
    },

    beforeCreate() { },
    created() { },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    data() {
        return {
            data: $MAPAS.faq,
            terms: '',
        }
    },

    computed: {
    },
    
    methods: {
        search() {
            let terms = this.terms.split(" ");            
            console.log(terms);
            console.log(this.data);
        }
    },
});
