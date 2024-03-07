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

        vacancies() {
            const firstPhase = this.phase.opportunity.parent ? this.phase.opportunity.parent : this.phase.opportunity;

            return firstPhase.vacancies;
        }
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

        getPercentage(option) {
            const val = this.geoQuota.distribution[option];
            return this.vacancies ? val / this.vacancies * 100 : 0;
        },

        setPercentage(option, $event) {
            const val = $event.target.value / 100 * this.vacancies;
            this.geoQuota.distribution[option] = val;
        },

        setDivision(option) {
            
            const distribution = {};
            const division = this.divisions.find((div) => div.metakey == option.value);
            const divisions = division.data;
            
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
