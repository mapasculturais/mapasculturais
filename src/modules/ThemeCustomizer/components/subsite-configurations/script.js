app.component('subsite-configurations', {
    template: $TEMPLATES['subsite-configurations'],

    props: {
        subsite: {
            type: Entity,
            required: true,
        },
    },

    async created() {
        if (!this.subsite.lang_config) {
            this.subsite.lang_config = [];
        }

        const ids = this.subsite.verifiedSeals.map((item) => item).join(',');
        if (ids) {
            this.selectEntityQuery = ids ? { id: `!IN(${ids})` } : {};
            
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

    data() {
        return {
            verifiedSeals: [],
            selectEntityQuery: '',
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
            console.log(this.verifiedSeals);
            return this.verifiedSeals;
        },
    },

    methods: {
        addSeal(seal) {
            this.subsite.verifiedSeals.push(seal._id);
            this.subsite.save();
        },
        removeSeal(seal) {
            this.subsite.verifiedSeals = this.subsite.verifiedSeals.filter(_seal => _seal !== seal._id);
            this.subsite.save();
        },
    },
});
