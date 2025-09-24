app.component('opportunity-exporter', {
    template: $TEMPLATES['opportunity-exporter'],
    
    // define os eventos que este componente emite
    emits: ['exported'],

    props: {
        opportunity: {
            type: Entity,
            required: true
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-exporter')
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
        opportunityPhases(){
            return $MAPAS.opportunityPhases;
        }
    },
    
    methods: {
        export () {

            // emite o evento enviando o data
            this.$emit('exported', this.opportunity);
        }
    },
});
