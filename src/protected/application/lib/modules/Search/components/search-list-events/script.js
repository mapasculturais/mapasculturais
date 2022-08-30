app.component('search-list-events', {
    template: $TEMPLATES['search-list-events'],
    
    created() {
        this.currentDate = null;
        this.eventApi = new API('event');
        this.spaceApi = new API('space');
        this.fetchOccurrences();
    },

    data() {
        return {
            occurrences: [],
            loading: false,
        }
    },

    watch: {
        pseudoQuery: {
            handler(){
                clearTimeout(this.watchTimeout);
                this.loading = true;

                this.watchTimeout = setTimeout(() => {
                    this.fetchOccurrences();
                }, 500)
            },
            deep: true,
        }
    },

    props: {
        limit: {
            type: Number,
            default: 20,
        },
        select: {
            type: String,
            default: 'id,name,subTitle,files.avatar,seals,terms,classificacaoEtaria,singleUrl'
        },
        spaceSelect: {
            type: String,
            default: 'id,name,endereco,files.avatar,singleUrl'
        },
        pseudoQuery: {
            type: Object,
            required: true
        }
    },

    methods: {
        // http://localhost/api/event/findOccurrences?@from=2022-08-19&@to=2022-09-19&space:@select=id,name,shortDescription,endereco&@select=
        async fetchOccurrences() {
            const query = Utils.parsePseudoQuery(this.pseudoQuery);

            this.loading = true;
            // clearTimeout(this.timeout);
            // this.timeout = setTimeout(() => {
                
            // }, 500)
            if(query['@keyword']) {
                query['event:@keyword'] = query['@keyword'];
                delete query['@keyword'];
            }
            query['event:@select'] = this.select;
            query['space:@select'] = this.spaceSelect;
            query['@limit'] = this.limit;
            
            this.occurrences = await this.eventApi.fetch('occurrences', query, {
                raw: true,
                rawProcessor: (rawData) => {
                    const data = rawData;
                    const event = this.eventApi.getEntityInstance(rawData.event.id); 
                    const space = this.spaceApi.getEntityInstance(rawData.space.id); 

                    event.populate(rawData.event);
                    space.populate(rawData.space);

                    data.event = event;
                    data.space = space;

                    data.starts = new McDate(rawData.starts.date);
                    data.ends = new McDate(rawData.ends.date);

                    return data;
                }
            });
            this.loading = false;
        },

        newDate(occurrence) {
            if (this.currentDate?.date('long') != occurrence.starts.date('long')) {
                this.currentDate = occurrence.starts;
                return true;
            } else {
                return false;
            }
        }
    },
});
