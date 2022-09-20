app.component('entity-occurrence-list', {
    template: $TEMPLATES['entity-occurrence-list'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-occurrence-list')
        return { text }
    },

    beforeCreate() { },
    created() {
        this.eventApi = new API('event');
        this.spaceApi = new API('space');
        this.fetchOccurrences();
    },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    props: {
        entity: {
            type: Entity,
            required: true,
        },
    },

    data() {
        return {
            occurrences: [],
            loading: false,
        }
    },

    computed: {
    },
    
    methods: {
        async fetchOccurrences() {
            const query = Utils.parsePseudoQuery(this.pseudoQuery);

            this.loading = true;
            if(query['@keyword']) {
                query['event:@keyword'] = query['@keyword'];
                delete query['@keyword'];
            }
            query['event:@select'] = 'id,name';
            query['space:@select'] = 'id,name';
            
            this.occurrences = await this.eventApi.fetch('occurrences', query, {
                raw: true,
                rawProcessor: (rawData) => Utils.occurrenceRawProcessor(rawData, this.eventApi, this.spaceApi)
            });
            this.loading = false;
        },
    },
});
