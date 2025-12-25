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
            handler() {
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
            default: 'id,parent_id,location,_geo_location,name,short_description,long_description,create_timestamp,status,type,agent_id,is_verified,public,update_timestamp,subsite_id'
        },
        pseudoQuery: {
            type: Object,
            required: true
        }
    },

    methods: {
        // http://localhost/api/event/findOccurrences?@from=2022-08-19&@to=2022-09-19&space:@select=id,name,shortDescription,endereco&@select=
        async fetchOccurrences(query = null) {
            if (query === null) {
                query = Utils.parsePseudoQuery(this.pseudoQuery);
            }

            this.loading = true;
            if (query['@keyword']) {
                query['event:@keyword'] = query['@keyword'];
                delete query['@keyword'];
            }

            if (query['@seals']) {
                query['event:@seals'] = query['@seals'];
                delete query['@seals'];
            }

            query['event:@select'] = this.select;
            query['space:@select'] = this.spaceSelect;
            query['@limit'] = this.limit;
            query['@page'] = this.page;

            try {
                const occurrences = await this.eventApi.fetch('occurrences', query, {
                    raw: true,
                    rawProcessor: (rawData) => Utils.occurrenceRawProcessor(rawData, this.eventApi, this.spaceApi)
                });

                const metadata = occurrences.metadata

                if (this.page === 1) {
                    this.occurrences = occurrences;
                } else {
                    this.occurrences = this.occurrences.concat(occurrences);
                    this.occurrences.metadata = metadata;
                }
                this.loading = false;
            } catch (error) {
                console.error('Erro ao buscar ocorrências:', error);
                this.loading = false;
                return;
            }
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
