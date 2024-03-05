
app.component('affirmative-policies--quota-configuration', {
    template: $TEMPLATES['affirmative-policies--quota-configuration'],

    props: {
        entity: {
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

    data() {
        const firstPhase = this.entity.opportunity.parent ?? this.entity.opportunity;
        return {
            totalVacancies: firstPhase.vacancies ?? 0,
            totalQuota: this.entity.quotaConfiguration ? this.entity.quotaConfiguration.vacancies : 0,
            totalPercentage: 0,
            fields: $MAPAS.config.affirmativePoliciesQuotaConfiguration.fields[this.entity.opportunity.id]
        }
    },

    methods: {
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

        setFieldName(option, quota) {
            quota.fieldName = option.value;
        },

        addConfig() {
            if (!this.entity.quotaConfiguration) {
                this.entity.quotaConfiguration = {
                    rules: [{
                        title: '',
                        vacancies: 0,
                        fields: [this.skeleton()]
                    }]
                };
            } else {
                this.entity.quotaConfiguration.rules.push({
                    vacancies: 0,
                    fields: [this.skeleton()]
                });
            }
        },
        addField(index) {
            this.entity.quotaConfiguration.rules[index].fields.push(this.skeleton());
        },
        skeleton() {
            const rules = {
                fieldName: '',
                eligibleValues: []
            }
            return rules;
        },
        removeConfig(item) {
            this.entity.quotaConfiguration.rules = this.entity.quotaConfiguration.rules.filter(function(value, key) {
                return item != key;
            });
            this.distributeQuotas(true);
        },
        removeField(ruleIndex, fieldIndex) {
            this.entity.quotaConfiguration.rules[ruleIndex].fields = this.entity.quotaConfiguration.rules[ruleIndex].fields.filter(function(value, key) {
                return fieldIndex != key;
            });
            if(this.entity.quotaConfiguration.rules[ruleIndex].fields.length === 0) {
                this.removeConfig(ruleIndex);
            } else {
                this.distributeQuotas(true);
            }
        },
        autoSave() {
            this.entity.save(3000)            
        },
        updateTotalQuotas() {
            this.totalQuota = (this.totalVacancies * this.totalPercentage) / 100;
        },
        updateQuotaPercentage() {
            this.totalPercentage = (this.totalQuota * 100) / this.totalVacancies;
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

            if(this.entity.quotaConfiguration && this.entity.quotaConfiguration.rules.length > 0 || removeQuota) {
                this.entity.quotaConfiguration.rules.forEach((quota, index) => {
                    countVacancies += quota.vacancies;
                });
                this.totalQuota = countVacancies;

                this.updateQuotaPercentage();
                if(!load) {
                    this.autoSave();
                }
            }
        }
    },

    mounted() {
        if(this.entity.quotaConfiguration && this.entity.quotaConfiguration.rules.length > 0) {
            if(this.totalVacancies > 0) {
                this.entity.quotaConfiguration.rules.forEach((quota, index) => {
                    this.updateRuleQuotaPercentage(quota, true);
                });
            } else {
                this.distributeQuotas(false, true);
            }
        }
    }
});
