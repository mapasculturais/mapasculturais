
app.component('affirmative-policies--quota-configuration', {
    template: $TEMPLATES['affirmative-policies--quota-configuration'],

    props: {
        phase: {
            type: Entity,
            required: true
        }
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const messages = useMessages();
        const text = Utils.getTexts('affirmative-policies--quota-configuration')
        
        return { text, messages }
    },

    mounted() {
        
        if(this.phase.quotaConfiguration && this.phase.quotaConfiguration.rules.length > 0) {
            if(this.totalVacancies > 0) {
                this.phase.quotaConfiguration.rules.forEach((quota, index) => {
                    this.updateRuleQuotaPercentage(quota, true);
                });
            } else {
                this.distributeQuotas(false, true);
            }
        }

        this.fixConfiguration();

        // salva as modificações
        this.$watch(() => this.phase.quotaConfiguration, (first, second) => {
            clearTimeout(this.autosaveTimeout);
            this.autosaveTimeout = setTimeout(() => {
                this.autoSave();
            }, this.autoSaveTime);

        }, { deep: true });


        // atualiza a configuração quando houver mudanças nos proponent types
        this.$watch(() => this.firstPhase.registrationProponentTypes, (first, second) => {
            this.fixConfiguration();
        }, { deep: true });
    },

    data() {
        const firstPhase = this.phase.opportunity.parent ?? this.phase.opportunity;

        return {
            autoSaveTime: 3000,
            firstPhase,
            totalVacancies: firstPhase.vacancies ?? 0,
            totalQuota: this.phase.quotaConfiguration ? this.phase.quotaConfiguration.vacancies : 0,
            totalPercentage: 0,
            fields: $MAPAS.config.affirmativePoliciesQuotaConfiguration.fields[this.phase.opportunity.id]
        }
    },

    computed: {
        isActive() {
            return this.phase?.quotaConfiguration?.rules.length > 0;
        },

        proponentTypes() {
            const firstPhase = this.firstPhase;
            const result = firstPhase.registrationProponentTypes.length ? firstPhase.registrationProponentTypes : ["default"];
            return result;
        }
    },

    watch: {
        
    },

    methods: {
        skeleton() {
            const fields = {};

            for(let proponentType of this.proponentTypes) {
                fields[proponentType] = {
                    fieldName: '',
                    eligibleValues: []
                }
            };

            return fields;
        },

        getQuotaField(proponentType,quota) {
            return quota.fields[proponentType] || {};
        },

        getField(quota) {
            const fieldName = quota.fieldName;
            const field = this.fields.find((field) => field.fieldName == fieldName);
            return field;
        },

        getFieldType(quota) {
            const field = this.getField(quota);
            return field?.fieldType;
        },

        getFieldOptions(quota) {
            const field = this.getField(quota);
            return field?.fieldOptions;
        },

        addConfig() {
            if (!this.phase.quotaConfiguration) {
                this.phase.quotaConfiguration = {
                    rules: [{
                        title: '',
                        vacancies: 0,
                        fields: this.skeleton()
                    }]
                };
            } else {
                this.phase.quotaConfiguration.rules.push({
                    vacancies: 0,
                    fields: this.skeleton()
                });
            }
        },

        removeConfig(item) {
            this.phase.quotaConfiguration.rules = this.phase.quotaConfiguration.rules.filter(function(value, key) {
                return item != key;
            });
            this.distributeQuotas(true);
        },
        
        updateTotalQuotas() {
            this.totalQuota = ((this.totalVacancies * this.totalPercentage) / 100).toFixed(2);
        },

        updateQuotaPercentage() {
            this.totalPercentage = ((this.totalQuota * 100) / this.totalVacancies).toFixed(2);
        },

        updateRuleQuotas(quota) {
            quota.vacancies = (this.totalVacancies * quota.percentage ) / 100;
            this.distributeQuotas();
        },

        updateRuleQuotaPercentage(quota, load = false) {
            quota.percentage = (quota.vacancies * 100) / this.totalVacancies;
            this.distributeQuotas(false, load);
        },

        distributeQuotas(deleteQuota = false, load = false) {
            let countVacancies = 0;
            let removeQuota = deleteQuota;

            if(this.phase.quotaConfiguration && this.phase.quotaConfiguration.rules.length > 0 || removeQuota) {
                this.phase.quotaConfiguration.rules.forEach((quota, index) => {
                    countVacancies += quota.vacancies;
                });
                this.totalQuota = countVacancies;

                this.updateQuotaPercentage();
            }
        },

        optionValue(option) {
            let _option = option.split(':');
            return _option[0];
        },

        optionLabel(option) {
            let _option = option.split(':');
            return _option.length > 1 ? _option[1] : _option[0];
        },

        autoSave(updated = false) {
            const filled = Object.values(this.phase.quotaConfiguration.rules).filter(
                quotaConfiguration => {
                    return quotaConfiguration.title !== undefined 
                        && quotaConfiguration.title 
                        && quotaConfiguration.vacancies !== undefined
                        && quotaConfiguration.vacancies > 0
                        // && quotaConfiguration.fields.some(field => 
                        //     field.eligibleValues !== undefined && field.eligibleValues.length > 0
                        //     && field.fieldName !== undefined && field.fieldName
                        // );
                }
            );
            
            if(filled.length || updated) {
                this.phase.save(this.autoSaveTime)            
            }
        },

        validateFields() {
            for (let quota of this.phase.quotaConfiguration.rules) {
                for (let field of Object.values(quota.fields)) {
                    // Verifica se o campo foi selecionado
                    if (!field.fieldName) {
                        return false;
                    }
                    // Verifica se pelo menos uma opção foi selecionada 
                    if ((this.getFieldType(field) === 'select' || this.getFieldType(field) === 'multiselect' || this.getFieldType(field) === 'checkboxes' || this.getFieldType(field) === 'boolean') &&
                        (!field.eligibleValues || field.eligibleValues.length === 0)) {
                        return false;
                    }
                }
            }
            return true;
        },

        updateField(quota, field) {
            const fieldData = this.getField(quota);
            if (fieldData) {
                field.eligibleValues = fieldData.eligibleValues;
            }
        },

        filteredOptions(proponentType) {
            const field = this.fields.filter(field => field.proponentTypes.includes(proponentType) || field.proponentTypes.length === 0);
            return field;
        },

        fixConfiguration() {
            const proponentTypes = this.proponentTypes;
            for (let quota of this.phase.quotaConfiguration?.rules || []) {
                for(let key in quota.fields) {
                    if(!proponentTypes.includes(key)) {
                        delete quota.fields[key];
                    }
                }
            }

            for (let quota of this.phase.quotaConfiguration?.rules || []) {
                for (let proponentType of proponentTypes) {
                    if(!quota.fields[proponentType]) {
                        quota.fields[proponentType] = {
                            fieldName: '',
                            eligibleValues: []
                        }
                    }
                }
            }
        }
    },
});
