app.component('search-map-event', {
    template: $TEMPLATES['search-map-event'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-map-event')
        return { text }
    },

    props: {
        pseudoQuery: {
            type: Object,
            default: {}
        },
    },

    data () {
        return {space: null};
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

        open ({entityPromise}) {
            entityPromise.then((entity) => {
                this.space = entity;
            })
        },

        close () {
            this.space = null;
        },

        newDate(occurrence) {
            if (this.currentDate?.date('long') != occurrence.starts.date('long')) {
                this.currentDate = occurrence.starts;
                return true;
            } else {
                return false;
            }
        },
    },
});
