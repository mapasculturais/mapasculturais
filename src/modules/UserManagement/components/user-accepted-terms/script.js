app.component('user-accepted-terms', {
    template: $TEMPLATES['user-accepted-terms'],
    emits: [],

    props: {
        user: {
            type: Entity,
            required: true
        },

        onlyTerm: {
            type: String
        }
    },
    
    computed: {
        terms() {
            let terms = {};
            if (this.onlyTerm) {
                terms[this.onlyTerm] = $MAPAS.config.LGPD[this.onlyTerm];
            } else {
                terms = $MAPAS.config.LGPD;
            }
            
            return terms;
        }
    },

    
    methods: {

        formatDate(timestamp) {
            let date = new McDate(new Date(timestamp * 1000));
            return date.date('numeric year') + ' - ' + date.time('numeric');
        }

        
    },
});
