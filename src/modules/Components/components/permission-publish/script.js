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

app.component('permission-publish', {
    template: $TEMPLATES['permission-publish'],
    
    // define os eventos que este componente emite
    // emits: ['namesDefined'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('__template__')
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
            displayName: this.nickname || this.name,
            fullname: this.fullname ? `${this.name} ${this.lastname}` : `${this.name}`,
            localizedData: this.text('texto localizado')
        }
    },

    computed: {
    
    },
    
    methods: {
     
    },
});
