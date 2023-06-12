app.component('opportunity-phase-support', {
    template: $TEMPLATES['opportunity-phase-support'],
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-phase-support')
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
            phases: $MAPAS.supportPhases,
        }
    },

    computed: {
    },
    
    methods: {
    },
});
