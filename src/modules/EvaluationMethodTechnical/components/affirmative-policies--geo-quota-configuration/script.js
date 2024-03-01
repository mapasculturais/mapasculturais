app.component('affirmative-policies--geo-quota-configuration', {
    template: $TEMPLATES['affirmative-policies--geo-quota-configuration'],

    props: {
        phase: {
            type: Entity,
            required: true,
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('affirmative-policies--geo-quota-configuration')
        return { text, hasSlot }
    },

    updated () {
        this.save();
    },

    data() {
        let geoQuota = this.phase.geoQuotaConfiguration || { geoDivision: '', distribution: {} };
        let isActive = !!Object.keys(geoQuota.distribution).length;
        
        return {
            isActive,
            geoQuota,
        }
    },

    computed: {
        divisions() {
            return $MAPAS.config.geoQuotaConfiguration;
        },
    },
    
    methods: {
        open() {
            this.isActive = true;
        },

        close() {
            this.geoQuota = { geoDivision: '', distribution: {} };
            this.save();
            this.isActive = false;
        },

        setDivision(option) {
            const distribution = {};
            const divisions = this.divisions[option.value].data;
            
            for (const option in divisions) {
                distribution[divisions[option]] = 0;
            }

            this.geoQuota.geoDivision = option.value;
            this.geoQuota.distribution = distribution;
        },

        async save() {
            this.phase.geoQuotaConfiguration = this.geoQuota;
            await this.phase.save(3000);
        }
    },
});
