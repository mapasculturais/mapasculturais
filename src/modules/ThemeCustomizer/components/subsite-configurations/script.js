app.component('subsite-configurations', {
    template: $TEMPLATES['subsite-configurations'],

    props: {
        subsite: {
            type: Entity,
            required: true,
        },
    },

    async created() {
        this.getSeals();
    },

    data() {
        return {
            verifiedSeals: [],
            selectEntityQuery: {},
        }
    },

    computed: {
        langs() {
            return {
                pt_BR: 'Português',
                es_ES: 'Español',
            }
        },

        langsLabels() {
            return Object.values(this.langs).map(function (lang) {
                return lang
            });
        },

        seals() {
            return this.verifiedSeals;
        },
    },

    methods: {
        getSeals() {
            this.selectEntityQuery = {};
            if (!this.subsite.lang_config) {
                this.subsite.lang_config = [];
            }
    
            const ids = this.subsite.verifiedSeals.map((item) => item).join(',');
            
            if (ids) {
                this.selectEntityQuery = { id: `!IN(${ids})` };
                
                const sealAPI = new API('seal');
    
                const query = {};
                query['@select'] = 'id,name,files.avatar';
                query['@order'] = 'id ASC';
                query['@permissions'] = '@control';
                query['id'] = `IN(${ids})`;
    
                Promise.all([sealAPI.find(query)])
                    .then((values) => {
                        this.verifiedSeals = values[0] ?? [];
                    });
            }
        },

        addSeal(seal) {
            this.subsite.verifiedSeals.push(seal._id);
            this.verifiedSeals = this.subsite.verifiedSeals;
            this.subsite.save(50, true);
            this.getSeals();
        },
        
        removeSeal(seal) {
            this.subsite.verifiedSeals = this.subsite.verifiedSeals.filter(_seal => _seal !== seal._id);
            this.verifiedSeals = this.subsite.verifiedSeals;
            this.getSeals();
            this.subsite.save(50, true);
        },
    },
});
