app.component('agent-table-2', {
    template: $TEMPLATES['agent-table-2'],

    props: {
        extraQuery: {
            type: Object,
            default: () => ({}),
        }, 
    },

    data() {
        return {
            additionalHeaders: $MAPAS.config.agentTable2.additionalHeaders
        }
    },

    computed: {
        

    },
    
    methods: {
        
    }
    
});
