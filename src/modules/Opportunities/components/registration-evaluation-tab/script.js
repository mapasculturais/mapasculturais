app.component('registration-evaluation-tab', {
    template: $TEMPLATES['registration-evaluation-tab'],

    props: {
        phaseId: {
            type: Number,
            required: true
        },
    },

    setup() {
        const text = Utils.getTexts('registration-evaluation-tab');
        return { text };
    },

    data() {
        return {
            valuers: $MAPAS.config.registrationEvaluationTab.valuers,
            groupedValuers: [],
            valuersIncludeList: [],
            valuersExcludeList: [],
        };
    },

    computed: {
        allValuers() {
            let result = [];
            const phaseId = this.phaseId;
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

            result.sort((a, b) => a.name.localeCompare(b.name));
            return result;
        },

        entity() {
            return $MAPAS.registrationPhases[this.phaseId] || {};
        },

        evaluations() {
            const evaluations = $MAPAS.config.registrationEvaluationTab.evaluations;
            return evaluations[this.phaseId];
        }
    },

    watch: {
        entity: {
            immediate: true,
            handler(entity) {
                if (entity?.valuers) {
                    this.initializeLists();
                    this.updateGroupedValuers();
                }
            }
        }
    },

    created() {
        this.initializeLists();
    },

    methods: {
        initializeLists() {
            this.valuersIncludeList = [...(this.entity.valuersIncludeList || [])];
            this.valuersExcludeList = [...(this.entity.valuersExcludeList || [])];
        },

        updateGroupedValuers() {
            if (!this.entity?.valuers) {
                this.groupedValuers = {};
                return;
            }

            const groups = {};

            Object.entries(this.entity.valuers).forEach(([userId, groupName]) => {
                if (!groups[groupName]) {
                    groups[groupName] = {
                        name: groupName,
                        isTiebreaker: groupName === '@tiebreaker',
                        valuers: []
                    };
                }
            });

            this.allValuers.forEach(valuer => {
                const groupName = this.entity.valuers[valuer.userId];
                if (groupName && groups[groupName]) {
                    groups[groupName].valuers.push({
                        ...valuer,
                    });
                }
            });

            this.groupedValuers = groups;
        },

        getValuerCommittees(valuer) {
            const committees = [];
            for (const [userId, groupName] of Object.entries(this.entity.valuers)) {
                if (parseInt(userId) === valuer.userId) {
                    committees.push(groupName === '@tiebreaker' ? this.text('Voto de minerva') : groupName);
                }
            }

            return committees.join(', ');
        },

        getStatusClass(statusText) {
            if (!statusText) return '';
            
            const statusMap = {
                'Avaliação pendente': 'mc-status--evaluation-pending',
                'Avaliação iniciada': 'mc-status--evaluation-started',
                'Avaliação concluída': 'mc-status--evaluation-completed',
                'Avaliação enviada': 'mc-status--evaluation-sent'
            };
            
            return statusMap[statusText] || '';
        },

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
        },

        async deleteEvaluation(evaluationId, userId) {
            if (!evaluationId) {
                return;
            }

            const evaluationData = this.evaluations[userId]?.evaluation;
            if (!evaluationData) {
                return;
            }

            try {
                const evaluationApi = new API('registrationevaluation');
                const evaluation = evaluationApi.getEntityInstance(evaluationId);
                evaluation.populate(evaluationData, true);
                
                await evaluation.delete();
                
                window.location.reload();
            } catch (error) {
                console.error(__('Erro ao excluir avaliação', 'registration-evaluation-tab'), error);
            }
        }
    },
});