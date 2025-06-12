
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
            country: null
        };
    },

    computed: {
        countries() {
            return $MAPAS.countries;
        },

        countryFieldEnabled() {
            return $MAPAS.config.countryLocalization.countryFieldEnabled;
        }
    },

    methods: {
        changeCountry() {
            this.entity.address_level0 = this.country;   
            this.clearFields();
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
        }
    },

    mounted() {
        this.country = this.entity.address_level0 ?? $MAPAS.config.countryLocalization.countryDefaultCode;
        this.entity.address_level0 = this.country;
    }
});
