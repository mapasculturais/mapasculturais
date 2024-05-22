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

app.component('metabase-dashboard', {
    template: $TEMPLATES['metabase-dashboard'],
    
    // define os eventos que este componente emite
    // emits: ['namesDefined'],

    props: {
        // entity: {
        //     type: Entity,
        //     required: true
        // },
    },
    
    setup({ slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('metabase-dashboard')
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
            links: $MAPAS.config.listDashboard.links,
        }
    },

    computed: {
        names() {
            const result = [];
            Object.keys(this.links).forEach(name => {
                result.push(name);
            })
            return result;
        },
    },
    
    methods: {
        //  this.$emit('namesDefined', this.entity);
    },
});
