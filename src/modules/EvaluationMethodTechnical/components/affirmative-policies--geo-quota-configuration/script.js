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
        const oppFirstPhase = this.phase.opportunity.parent ?? this.phase.opportunity;

        return {
            isActive,
            geoQuota,
            oppFirstPhase,
            hasProponentType: oppFirstPhase.registrationProponentTypes && oppFirstPhase.registrationProponentTypes.length > 0,
            hasCollective: oppFirstPhase.registrationProponentTypes.includes('Coletivo'),
            hasMEI: oppFirstPhase.registrationProponentTypes.includes('MEI'),
            hasNaturalPerson: oppFirstPhase.registrationProponentTypes.includes('Pessoa Física'),
            hasLegalEntity: oppFirstPhase.registrationProponentTypes.includes('Pessoa Jurídica')
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
            this.save(true);
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

        setGeoQuotaField(option, proponentType) { 
            this.geoQuota.fields[`${proponentType}`] = option.value;
        },

        getFields(proponentType = '') {
            const affirmativePoliciesEligibleFields = this.phase.opportunity.parent.affirmativePoliciesEligibleFields ?? this.phase.opportunity.affirmativePoliciesEligibleFields;
            return affirmativePoliciesEligibleFields.filter(field => {
                if (proponentType === '') {
                    return !field.proponentTypes || field.proponentTypes.length == 0;
                } else {
                    return (!field.proponentTypes || field.proponentTypes.length == 0) 
                            || (field.proponentTypes && field.proponentTypes.includes(proponentType));
                }
            });
        },

        async save(updated = false) {
            if(updated) {
                this.geoQuota = { geoDivision: '', distribution: {} };
                this.phase.geoQuotaConfiguration = this.geoQuota;
            }

            if(this.geoQuota.geoDivision !== '' 
                && this.geoQuota
                && this.geoQuota.distribution !== null
            ) {
                this.phase.geoQuotaConfiguration = this.geoQuota;
            }

            await this.phase.save(3000);
        },
    },

    mounted() {
        if (!this.geoQuota.fields || typeof this.geoQuota.fields !== 'object') {
            this.geoQuota.fields = {};
        }
    }
});
