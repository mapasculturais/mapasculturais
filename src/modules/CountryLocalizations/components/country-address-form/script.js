
app.component('country-address-form', {
    template: $TEMPLATES['country-address-form'],
    emits: [],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        return {
            country: null,
            levelHierarchy: null,
            processing: false
        };
    },

    computed: {
        countries() {
            return Object.entries($MAPAS.countries).map(([code, country]) => ({
                label: country,
                value: code,
            }));
        },

        countryFieldEnabled() {
            return $MAPAS.config.countryLocalization.countryFieldEnabled;
        },

        isCountryRequired() {
            const description = this.entity.$PROPERTIES?.address_level0 || {};
            return description.required || false;
        }
    },

    methods: {
        changeCountry() {
            this.processing = true;
            this.entity.address_level0 = this.country;   
            this.clearFields();

            this.$nextTick(() => {
                this.getLevelHierarchy();
            });
        },

        clearFields() {
            this.entity.address_postalCode = null;
            this.entity.address_level1 = null;
            this.entity.address_level2 = null;
            this.entity.address_level3 = null;
            this.entity.address_level4 = null;
            this.entity.address_level5 = null;
            this.entity.address_level6 = null;
            this.entity.address_line1 = null;
            this.entity.address_line2 = null;
        },

        async getLevelHierarchy() {
            const api = new API('country-localization');

            let data = {
                country: this.country,
            }
            let url = api.createApiUrl('findLevelHierarchy', data);

            await api.GET(url, data).then(res => res.json()).then(data => {
                this.levelHierarchy = data.error ? null : data;
                this.processing = false;
            });
        }
    },

    mounted() {
        this.country = this.entity.address_level0 ?? $MAPAS.config.countryLocalization.countryDefaultCode;
        this.entity.address_level0 = this.country;
        this.getLevelHierarchy();
    },
});
