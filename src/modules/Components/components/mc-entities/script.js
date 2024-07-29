app.component('mc-entities', {
    template: $TEMPLATES['mc-entities'],
    emits: ['fetch'],

    data() {
        return {
            api: new API(this.type, this.scope || 'default'),
            entities: [],
            page: 1
        }
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-entities')
        return { text }
    },

    created() {
        if(this.name) {
            this.api.lists.store(this.name, this.entities, this.scope || 'default');
        }

        this.populateQuery(this.query);

        this.entities.query = this.query;
        this.entities.metadata = {};
        this.entities.loading = false;
        this.entities.loadingMore = false;
        this.entities.refresh = (debounce) => this.refresh(debounce);
        this.entities.loadMore = () => this.loadMore();
        this.entities.stringifiedQuery = JSON.stringify(this.entities.query)
        if (this.watchQuery) {
            this.$watch(() => ({
                select: this.select,
                ids: this.ids,
                limit: this.limit,
                query: this.query,
                order: this.order,
            }), () => this.entities.refresh(this.watchDebounce), {deep:true});
        }

        this.refresh();
    },

    props: {
        name: String,
        type: {
            type: String,
            required: true
        },
        select: String,
        ids: Array,
        query: {
            type: Object || String,
            default: {}
        },
        limit: Number,
        permissions: String,
        order: {
            type: String,
            default: 'id ASC'
        },
        watchQuery: {
            type: Boolean,
            default: false
        },
        watchDebounce: {
            type: Number,
            default: 500
        },
        endpoint: {
            type: String,
            default: 'find'
        },
        rawProcessor: Function,
        scope: {
            type: String,
            default: 'default'
        }

    },
    
    methods: {
        populateQuery(query) {
            if (this.select) {
                query['@select'] = this.select;
            } 
    
            if (this.ids) {
                query[this.API.$PK] = 'IN(' + this.ids.join(',') + ')'
            }

            if (this.order) {
                query['@order'] = this.order; 
            }
    
            if (this.limit) {
                query['@limit'] = this.limit;
                query['@page'] = this.page;
            }
    
            if (this.permissions) {
                query['@permissions'] = this.permissions;
            }
        },

        getDataFromApi() {
            let query = {...this.query};
            this.populateQuery(query);

            const options = {list: this.entities, refresh: true};

            if (this.limit && this.page) {
                query['@page'] = this.page;
            }
            
            if (this.rawProcessor) {
                options.raw = true;
                options.rawProcessor = this.rawProcessor;
            };

            const result = this.api.fetch(this.endpoint, query, options);

            result.then((entities) => {
                this.$emit('fetch', entities);
            })

            return result;
        },
        
        refresh(debounce) {
            debounce = debounce || 0;
            this.page = 1;
            this.entities.loading = true;
            this.entities.splice(0);
            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                this.getDataFromApi().then(() => { 
                    this.entities.loading = false;
                });
            }, debounce);
        },

        loadMore() {
            if (!this.limit) {
                console.error('Tentado obter mais resultados em consulta sem paginação');
                return;
            }
            this.page++;
            this.entities.loadingMore = true;
            this.getDataFromApi().then(() => { 
                this.entities.loadingMore = false;
            });
        },

        showLoadMore() {
            return this.entities.length > 0 && this.entities.metadata?.page < this.entities.metadata?.numPages;
        }

    },
});