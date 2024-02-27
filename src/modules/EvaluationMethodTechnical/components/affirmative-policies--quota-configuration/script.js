
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
        return {
            selectedField: [],
            timeout: null,
            totalVacancies: this.entity.opportunity.vacancies ?? 0,
            totalQuota: this.entity.quotaConfiguration ? this.entity.quotaConfiguration.vacancies : 0,
            totalPercentage: 0,
            rulesPercentages: []
        }
    },

    methods: {
        addConfig() {
            if (!this.entity.quotaConfiguration) {
                this.entity.quotaConfiguration = {
                    vacancies: 0,
                    rules: [this.skeleton()]
                };
            } else {
                this.entity.quotaConfiguration.rules.push(this.skeleton());
            }
        },
        skeleton() {
            const rules = {
                fieldName: '',
                vacancies: 0,
                eligibleValues: []
            }
            return rules;
        },
        selectField(fieldName, index, load = false) {
            this.selectedField[index] = this.entity.opportunity.affirmativePoliciesEligibleFields.find(item => item.fieldName === fieldName);
            if (!load) {
                this.autoSave();
            }
        },
        removeConfig(item) {
            this.entity.quotaConfiguration.rules = this.entity.quotaConfiguration.rules.filter(function(value, key) {
                return item != key;
            });
            this.autoSave();
        },
        autoSave() {
            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                this.entity.save()
            }, 300);
        },
        updateTotalQuotas() {
            this.totalQuota = (this.totalVacancies * this.totalPercentage) / 100;
            this.entity.quotaConfiguration.vacancies = this.totalQuota;
        },
        updateQuotaPercentage() {
            this.totalPercentage = (this.totalQuota * 100) / this.totalVacancies;
            this.entity.quotaConfiguration.vacancies = this.totalQuota;
        },
        updateRuleQuotas(quota, index) {
            quota.vacancies = (this.totalQuota * this.rulesPercentages[index] ) / 100;
            this.distributeQuotas();
        },
        updateRuleQuotaPercentage(quota, index, load = false) {
            this.rulesPercentages[index] = (quota.vacancies * 100) / this.totalQuota;
            this.distributeQuotas(load);
        },
        distributeQuotas(load) {
            let countVacancies = 0;
            if(this.entity.quotaConfiguration && this.entity.quotaConfiguration.rules.length > 0) {
                this.entity.quotaConfiguration.rules.forEach((quota, index) => {
                    countVacancies += quota.vacancies;
                });

                if(countVacancies > this.totalQuota) {
                    this.messages.error(this.text('limitQuota'));
                } else {
                    if(!load) {
                        this.autoSave();
                    }
                }
            }
        }
    },

    mounted() {
        if(this.entity.quotaConfiguration && this.entity.quotaConfiguration.rules.length > 0) {
            this.updateQuotaPercentage();
            this.entity.quotaConfiguration.rules.forEach((quota, index) => {
                this.updateRuleQuotaPercentage(quota, index, true);
                this.selectField(quota.fieldName, index, true);
            });
        }
    }
});
