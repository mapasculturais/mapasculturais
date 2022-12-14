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
        // Termo
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
    computed: {
        // politica de privacidade
        
       
    },

    methods: {
        formatDate(timestamp){
            const date = new Date(timestamp*1000);
            const mcDate = new cDate(date);
            return mcDate.date('numeric year') + ' - ' + mcDate.time('numeric');
        }
    },
});
