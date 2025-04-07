app.component('registration-evaluation-tab', {
    template: $TEMPLATES['registration-evaluation-tab'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    setup() {
        const text = Utils.getTexts('registration-evaluation-tab');
        return { text };
    },

    data() {
        console.log("valuers do init.php =>", $MAPAS.config.registrationEvaluationTab);
        return {
            valuers: $MAPAS.config.registrationEvaluationTab,
            valuersIncludeList: this.entity.valuersIncludeList || [],
            valuersExcludeList: this.entity.valuersExcludeList || [],
        };
    },

    computed: {
        allValuers() {
            let result = [];
            for (const phaseId in this.valuers) {
                const phaseValuers = this.valuers[phaseId];
                for (const userId in phaseValuers) {
                    const valuer = phaseValuers[userId];
                    result.push({
                        id: valuer.id,
                        userId: parseInt(userId),
                        phaseId: parseInt(phaseId),
                        name: valuer.name,
                        ...valuer
                    });
                }
            }
            console.log("todos os valuers da op =>", result);
            return result;
        }
    },

    created() {
        this.valuersIncludeList = this.entity.valuersIncludeList || [];
        this.valuersExcludeList = this.entity.valuersExcludeList || [];
    },

    methods: {
        isIncluded(valuerId) {
            return this.valuersIncludeList.includes(valuerId);
        },

        isExcluded(valuerId) {
            return this.valuersExcludeList.includes(valuerId);
        },

        toggleValuer(valuer, isChecked, listType) {
            console.log(valuer);
            const valuerId = valuer.userId;

            if (listType === 'include') {
                if (isChecked) {
                    this.addValuerInclude(valuerId);
                } else {
                    this.removeValuerInclude(valuerId);
                }
            } else {
                if (isChecked) {
                    this.addValuerExclude(valuerId);
                } else {
                    this.removeValuerExclude(valuerId);
                }
            }

            this.saveLists();
        },

        addValuerInclude(valuerId) {
            if (!this.valuersIncludeList.includes(valuerId)) {
                this.valuersIncludeList.push(valuerId);
                this.removeValuerExclude(valuerId);
            }
        },

        removeValuerInclude(valuerId) {
            this.valuersIncludeList = this.valuersIncludeList.filter(id => id !== valuerId);
        },

        addValuerExclude(valuerId) {
            if (!this.valuersExcludeList.includes(valuerId)) {
                this.valuersExcludeList.push(valuerId);
                this.removeValuerInclude(valuerId);
            }
        },

        removeValuerExclude(valuerId) {
            this.valuersExcludeList = this.valuersExcludeList.filter(id => id !== valuerId);
        },

        saveLists() {
            this.entity.valuersIncludeList = this.valuersIncludeList;
            this.entity.valuersExcludeList = this.valuersExcludeList;
            this.entity.save(); 
        }
    },
});