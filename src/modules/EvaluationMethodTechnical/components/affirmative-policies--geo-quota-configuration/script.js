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

    mounted() {
        if (!this.geoQuota.fields || typeof this.geoQuota.fields !== 'object') {
            this.geoQuota.fields = {};
        }
    },
    
    updated () {
        this.save();
    },

    data() {
        let geoQuota = this.phase.geoQuotaConfiguration || { geoDivision: '', distribution: {} };
        let isActive = !!Object.keys(geoQuota.distribution).length;
        const oppFirstPhase = this.phase.opportunity.parent ?? this.phase.opportunity;
        let autosaveTime = 3000;

        return {
            totalQuota: 0,
            fields: {},
            isActive,
            geoQuota,
            oppFirstPhase,
            hasProponentType: oppFirstPhase.registrationProponentTypes && oppFirstPhase.registrationProponentTypes.length > 0,
            hasCollective: oppFirstPhase.registrationProponentTypes.includes('Coletivo'),
            hasMEI: oppFirstPhase.registrationProponentTypes.includes('MEI'),
            hasNaturalPerson: oppFirstPhase.registrationProponentTypes.includes('Pessoa Física'),
            hasLegalEntity: oppFirstPhase.registrationProponentTypes.includes('Pessoa Jurídica'),
            autosaveTime,
        }
    },

    computed: {
        divisions() {
            return $MAPAS.config.geoQuotaConfiguration.geoDivisions;
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

        trash() {
            this.autosaveTime = 600;
            this.save(true);
            this.isActive = false;
        },

        getPercentage(option) {
            const val = this.geoQuota.distribution[option];
            return this.vacancies ? val / this.vacancies * 100 : 0;
        },

        sumGeoQuota(option) {
            this.totalQuota = 0;
            Object.values(this.geoQuota.distribution).forEach((item) => {
                this.totalQuota += item;
            });
            console.log(this.totalQuota)
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

        setGeoQuotaField(option, proponentType) { 
            this.autosaveTime = 600;
            this.fields[proponentType] = option.value;
            this.geoQuota.fields = this.fields;
            this.phase.geoQuotaConfiguration = this.geoQuota;
        },

        getFields(proponentType = '') {
            const opportunity = this.phase.opportunity;
            const fields = $MAPAS.config.geoQuotaConfiguration.fields[opportunity.id];

            const result = fields.filter((field) => {
                if (proponentType === '') {
                    return !field.proponentTypes || field.proponentTypes.length == 0;
                } else {
                    return (!field.proponentTypes || field.proponentTypes.length == 0) 
                            || (field.proponentTypes && field.proponentTypes.includes(proponentType));
                }
            });

            return result;
        },
        async save(trash = false) {
            if(trash) {
                this.geoQuota = { geoDivision: '', distribution: {}, fields: {} };
                this.phase.geoQuotaConfiguration = this.geoQuota;
            }

            if(this.geoQuota.geoDivision !== '' 
                && this.geoQuota
                && this.geoQuota.distribution !== null
                || trash
            ) {
                this.phase.geoQuotaConfiguration = this.geoQuota;
                await this.phase.save(this.autosaveTime);
            }

            this.autosaveTime = 3000;
        },
    },
});
