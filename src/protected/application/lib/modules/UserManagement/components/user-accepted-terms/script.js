app.component('user-accepted-terms', {
    template: $TEMPLATES['user-accepted-terms'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        const terms = $MAPAS.config.LGPD;

        return {
            terms
        };
    },

    props: {
        user: {
            type: Entity,
            required: true
        },
    },
    
    methods: {
        
    },
});
