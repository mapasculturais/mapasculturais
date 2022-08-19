app.component('search-map', {
    template: $TEMPLATES['search-map'],
    
    async mounted(){
        this.api = new API(this.type);
        this.fetchEntities();
    },

    watch: {
        pseudoQuery: {
            handler(pseudoQuery){
                this.entities = [];
                this.loading = true;
                clearTimeout(this.refreshTimeout);

                this.refreshTimeout = setTimeout(() => {
                    this.fetchEntities();
                }, 500)
            },
            deep: true,
        }
    },
    
    data() {
        return {
            loading: false,
            entities: [],
        }
    },
    
    props: {
        type: {
            type: String,
            required: true,
        },
        pseudoQuery: {
            type: Object,
            default: {}
        },
    },

    methods: {
        async fetchEntities() {
            const query = Utils.parsePseudoQuery(this.pseudoQuery);
            query['@select'] = 'id,type,name,location,singleUrl';
            query['location'] = query['location'] || '!EQ([0,0])';
            
            this.entities = [];
            this.loading = true;
            this.entities = await this.api.find(query, null, true);
            this.loading = false;
        },

        closePopup() {
            console.log('close');
        }
    },
});
