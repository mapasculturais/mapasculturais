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
        editable: {
            type: Boolean,
            default: false
        },
        pseudoQuery: {
            type: Object,
            default: {}
        },
    },

    data() {
        return {
            occurrences: [],
            loading: false,
            space: this.entity /* Alterar do this.entity para o espaço vinculado */
        }
    },

    computed: {
        spaceQuery() {
            const query = Utils.parsePseudoQuery({
                'space:id': this.space.id,
                ...this.pseudoQuery
            });

            query['event:@select'] = 'id,name,terms,files.avatar,classificacaoEtaria';

            return query;
        }
    },
    
    methods: {
        spaceRawProcessor (entity) {
            entity = Utils.entityRawProcessor(entity);
            entity['@icon'] = 'event';
            
            return entity;
        },

        occurrenceRawProcessor (entity) {
            return Utils.occurrenceRawProcessor(entity, this.eventApi);
        },
    },
});
