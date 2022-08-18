app.component('search-list', {
    template: $TEMPLATES['search-list'],
    
    data() {
        return {
            entitiesQuery: {}
        }
    },

    watch: {
        query: {
            handler(query){
                clearTimeout(this.refreshTimeout);

                this.refreshTimeout = setTimeout(() => {
                    const newQuery = {};
                    for(let k in query) {
                        let val = query[k];
                        if(k == '@verified') {
                            if (val) {
                                newQuery[k] = '1';
                            }
                        } else if(k == '@keyword') {
                            val = val.replace(/ +/g, '%');
                            newQuery[k] = `${val}`;
                        } else if(val) {
                            if (typeof val == 'string') {
                                newQuery[k] = `EQ(${val})`;
                            } else if (val instanceof Array) {
                                val = val.join(',');
                                newQuery[k] = `IIN(${val})`;
                            }
                        }
                    }
                    this.entitiesQuery = newQuery;
                }, 500)
            },
            deep: true,
        }
    },

    props: {
        type: {
            type: String,
            required: true,
        },
        limit: {
            type: Number,
            default: 20,
        },
        select: {
            type: String,
            default: 'id,name,shortDescription,files.avatar,seals,terms,singleUrl'
        },
        api: {
            type: API,
            required: true
        },
        query: {
            type: Object,
            required: true
        }
    },

    methods: {

    },
});
