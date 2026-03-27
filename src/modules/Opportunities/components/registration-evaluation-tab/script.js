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
            if (!phaseValuers || typeof phaseValuers !== 'object') {
                return result;
            }
            for (const userId in phaseValuers) {
                const valuer = phaseValuers[userId];
                result.push({
                    id: valuer.id,
                    userId: parseInt(userId),
                    phaseId: parseInt(phaseId),
                    name: valuer.name,
                    groups: Array.isArray(valuer.groups) ? valuer.groups : (valuer.group ? [valuer.group] : []),
                    ...valuer
                });
            }
            result.sort((a, b) => a.name.localeCompare(b.name));
            return result;
        },

        valuersByGroup() {
            const result = {};
            this.allValuers.forEach((valuer) => {
                const groups = Array.isArray(valuer.groups) ? valuer.groups : [];
                groups.forEach((groupName) => {
                    if (!result[groupName]) {
                        result[groupName] = [];
                    }
                    result[groupName].push({
                        ...valuer,
                        group: groupName,
                        valuerKey: `${valuer.userId}::${groupName}`,
                    });
                });
            });

            Object.keys(result).forEach((groupName) => {
                result[groupName].sort((a, b) => a.name.localeCompare(b.name));
            });

            return result;
        },

        entity() {
            return $MAPAS.registrationPhases[this.phaseId] || {};
        },

        evaluations() {
            const evaluations = $MAPAS.config.registrationEvaluationTab?.evaluations || {};
            return evaluations[this.phaseId] || {};
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
            const normalizeIds = (list) => {
                const ids = (list || [])
                    .map(id => parseInt(id, 10))
                    .filter(id => !Number.isNaN(id));
                
                return ids.filter((id, index) => ids.indexOf(id) === index);
            };

            this.valuersIncludeList = normalizeIds(this.entity.valuersIncludeList);
            this.valuersExcludeList = normalizeIds(this.entity.valuersExcludeList);
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

            const seenByGroup = {};
            this.allValuers.forEach(valuer => {
                const groupName = this.entity.valuers[valuer.userId];
                if (groupName && groups[groupName]) {
                    seenByGroup[groupName] = seenByGroup[groupName] || new Set();
                    if (seenByGroup[groupName].has(valuer.userId)) {
                        return;
                    }
                    seenByGroup[groupName].add(valuer.userId);
                    groups[groupName].valuers.push({
                        ...valuer,
                    });
                }
            });

            this.groupedValuers = groups;
        },

        getValuerCommittees(valuer) {
            const groups = Array.isArray(valuer.groups) ? valuer.groups : [];
            const committees = groups.map((groupName) => {
                return groupName === '@tiebreaker' ? this.text('Voto de minerva') : groupName;
            });

            if (!committees.length) {
                for (const [userId, groupName] of Object.entries(this.entity.valuers || {})) {
                    if (parseInt(userId) === valuer.userId) {
                        committees.push(groupName === '@tiebreaker' ? this.text('Voto de minerva') : groupName);
                    }
                }
            }
            return committees.join(', ');
        },

        getGroupName(groupName) {
            return groupName === '@tiebreaker' ? this.text('Voto de minerva') : groupName;
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

        isIncluded(valuer, groupName = null) {
            const valuerId = parseInt(valuer.userId, 10);
            const inIncludeList = this.valuersIncludeList.includes(valuerId);
            if (!inIncludeList) return false;

            const currentGroup = groupName || valuer.group;
            const selectedGroup = this.entity?.valuers?.[valuerId] ?? this.entity?.valuers?.[String(valuerId)];
            return selectedGroup === currentGroup;
        },

        isExcluded(valuer, groupName = null) {
            const valuerId = parseInt(valuer.userId, 10);
            return this.valuersExcludeList.includes(valuerId);
        },

        toggleValuer(valuer, isChecked, listType, groupName = null) {
            const valuerId = valuer.userId;
            const valuerGroup = groupName || valuer.group;

            if (!this.entity.valuers || typeof this.entity.valuers !== 'object') {
                this.entity.valuers = {};
            }

            if (listType === 'include') {
                if (isChecked) {
                    this.removeValuerExclude(valuerId);
                    this.addValuerInclude(valuerId);
                    this.entity.valuers[valuerId] = valuerGroup;
                } else {
                    const currentSelected = this.entity.valuers[valuerId] ?? this.entity.valuers[String(valuerId)];
                    if (currentSelected === valuerGroup) {
                        delete this.entity.valuers[valuerId];
                        delete this.entity.valuers[String(valuerId)];
                        this.removeValuerInclude(valuerId);
                    }
                }
            } else {
                if (isChecked) {
                    this.removeValuerInclude(valuerId);
                    this.addValuerExclude(valuerId);
                    delete this.entity.valuers[valuerId];
                    delete this.entity.valuers[String(valuerId)];
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