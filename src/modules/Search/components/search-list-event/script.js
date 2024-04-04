app.component('search-list-event', {
    template: $TEMPLATES['search-list-event'],
    
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
            page: 1
        }
    },

    watch: {
        pseudoQuery: {
            handler(){
                clearTimeout(this.watchTimeout);
                this.loading = true;
                this.page = 1;

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
            query['@page'] = this.page;
            
            const occurrences = await this.eventApi.fetch('occurrences', query, {
                raw: true,
                rawProcessor: (rawData) => Utils.occurrenceRawProcessor(rawData, this.eventApi, this.spaceApi)
            });
            
            const metadata = occurrences.metadata;

            if(this.page === 1) {
                this.occurrences = occurrences;
            } else {
                this.occurrences = this.occurrences.concat(occurrences);
                this.occurrences.metadata = metadata;
            }
            this.loading = false;
        },

        loadMore() {
            this.page++;
            this.fetchOccurrences();
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
