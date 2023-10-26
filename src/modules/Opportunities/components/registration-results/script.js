app.component('registration-results', {
    template: $TEMPLATES['registration-results'],

    props: {
        /* entity: {
            type: Entity,
            required: true
        }, */
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-results')
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
           
        }
    },

    computed: {
    },
    
    methods: {
    },
});
