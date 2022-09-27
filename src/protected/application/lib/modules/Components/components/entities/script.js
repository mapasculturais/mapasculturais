/**
 * uso:
 * 
 * // omitindo o id, pega a entity do Mapas.requestedEntity
 * <entity v-slot='{entity}'>{{entity.id}} - {{entity.name}}</entity>
 * 
 * // passango o id sem select, não faz consulta na api
 * <entity v-slot='{entity}' :type='space' :id='33'>{{entity.id}}</entity>
 * 
 * // passando o id e passando um select, faz a consulta na api
 * <entity v-slot='{entity}' :type='space' :id='33' :select="id,name">{{entity.id}} - {{entity.name}}</entity>
 */
app.component('entities', {
    template: $TEMPLATES['entities'],

    data() {
        return {
            api: new API(this.type, this.scope || 'default'),
            entities: [],
            page: 1
        }
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        return { hasSlot }
    },

    created() {
        if(this.name) {
            this.api.lists.store(this.name, this.entities);
        }

        if (this.select) {
            this.query['@select'] = this.select;
        } 

        if (this.ids) {
            this.query.id = 'IN(' + this.ids.join(',') + ')'
        }

        if (this.order) {
            this.query['@order'] = this.order; 
        }

        this.entities.metadata = {};
        this.entities.loading = false;
        this.entities.loadingMore = false;
        this.entities.refresh = (debounce) => this.refresh(debounce);
        this.entities.loadMore = () => this.loadMore();
        this.entities.query = this.query;
        this.entities.stringifiedQuery = JSON.stringify(this.entities.query)
        let watchTimeout = null;
        if (this.watchQuery) {
            this.$watch('query', (q1) => {
                if(this.entities.stringifiedQuery == JSON.stringify(q1)) {
                    return;
                }
                this.entities.stringifiedQuery = JSON.stringify(q1);
                this.entities.loading = true;
                this.entities.splice(0);
                clearTimeout(watchTimeout, 100);
                watchTimeout = setTimeout(() => {
                    this.entities.refresh();
                }, this.watchDebounce);
            }, {deep:true});
        }

        this.refresh();
    },

    props: {
        name: String,
        ids: Array,
        type: {
            type: String,
            required: true
        },
        select: String,
        query: {
            type: Object,
            default: () => ({})
        },
        limit: Number,
        order: String,
        scope: String,
        watchQuery: Boolean,
        watchDebounce: {
            type: Number,
            default: 500
        },
        endpoint: {
            type: String,
            default: 'find'
        },
        rawProcessor: {
            type: Function,
            required: false
        }
    },
    
    methods: {
        getDataFromApi() {
            let query = {...this.query};

            if (this.limit) {
                query['@limit'] = this.limit;
                query['@page'] = this.page;
            }
            const options = {list: this.entities};
            if (this.rawProcessor) {
                options.raw = true;
                options.rawProcessor = this.rawProcessor;
            };
            return this.api.fetch(this.endpoint, query, options);
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
