app.component('search-list', {
    template: $TEMPLATES['search-list'],
    
    data() {
        return {
            query: {}
        }
    },

    watch: {
        pseudoQuery: {
            handler(pseudoQuery){
                clearTimeout(this.refreshTimeout);

                this.refreshTimeout = setTimeout(() => {
                    const newQuery = {};
                    for(let k in pseudoQuery) {
                        let val = pseudoQuery[k];
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
                    this.query = newQuery;
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
        pseudoQuery: {
            type: Object,
            required: true
        }
    },

    methods: {

    },
});
