app.component('opportunity-committee-groups', {
    template: $TEMPLATES['opportunity-committee-groups'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    data() {
        return {
            editable: true,
            newGroupName: '',
            groups: this.entity.relatedAgents || {},
            editGroupName: false,
            newName: '',
            localSubmissionEvaluatorCount: {},
            tabSelected: '',
            hasGroupsFlag: false,
            minervaGroup: 'Comissão de voto final',
            globalExcludeFields: [],
            individualExcludeFields: [],
            selectedFields: {
                global: '',
                individual: ''
            }
        }
    },

    computed: {
        hasTwoOrMoreGroups() {
            const filteredGroups = Object.keys(this.groups).filter(group => group !== this.minervaGroup);
            const groupCount = filteredGroups.length;
            return groupCount >= 2 && !this.groups[this.minervaGroup];
        }
    },

    mounted() {
        this.initializeGroups();
        this.initiliazeSubmissionEvaluatorCount();
    },
    
    methods: {
        updateExcludedFields(group, selectedField) {
            this.selectedFields[group] = selectedField;

            if (group === 'global') {
                this.individualExcludeFields = this.selectedFields.global;
            } else if (group === 'individual') {
                this.globalExcludeFields = this.selectedFields.individual;
            }
        },

        initializeGroups() {
            let groups = {};

            if (!groups['Comissão de avaliação'] && Object.keys(this.groups).length === 0) {
                groups['Comissão de avaliação'] = {};
            }
            
            for (let groupName of Object.keys(this.groups)) {
                if (groupName !== "group-admin" && groupName !== '@support') {
                    groups[groupName] = this.groups[groupName];
                }
            }
            this.groups = groups;
        },

        initiliazeSubmissionEvaluatorCount() {
            if(!this.entity?.submissionEvaluatorCount) {
                this.entity.submissionEvaluatorCount = {};
            }
            
            // Se não existir configuração de filtro para avaliadores, cria objeto vazio 
            if(!this.entity?.registrationFilterConfig) {
                this.entity.registrationFilterConfig = {};
            }
            
            Object.keys(this.groups || {}).forEach(groupName => {
                if(this.entity.submissionEvaluatorCount[groupName] && !this.localSubmissionEvaluatorCount[groupName]) {
                    this.localSubmissionEvaluatorCount[groupName] = this.entity.submissionEvaluatorCount[groupName];
                }

                if (!this.entity.submissionEvaluatorCount[groupName] && !this.localSubmissionEvaluatorCount[groupName]) {
                    this.localSubmissionEvaluatorCount[groupName] = null;
                }
            });

            this.entity.submissionEvaluatorCount = this.localSubmissionEvaluatorCount;

            this.entity.save();
        },

        hasGroups() {
            return this.hasGroupsFlag;
        },

        addGroup(group, disableMinervaGroup = false) {
            if (!this.entity.relatedAgents[group]) {
                this.entity.relatedAgents[group] = [];
            }

            if (!this.entity.agentRelations[group]) {
                this.entity.agentRelations[group] = [];
            }

            this.groups = { ...this.groups, [group]: this.entity.agentRelations[group] };

            this.localSubmissionEvaluatorCount[group] = null;

            if(!this.entity?.registrationFilterConfig) {
                this.entity.registrationFilterConfig = {}
            }

            if(!this.entity?.registrationFilterConfig[group]) {
                this.entity.registrationFilterConfig[group] = {}
            }

            this.reorderGroups();
        },

        removeGroup(group) {
            delete this.groups[group]
            delete this.localSubmissionEvaluatorCount[group];
            this.entity.removeAgentRelationGroup(group);

            this.autoSave();
        },

        updateGroupName(oldGroupName, newGroupName) {
            if (!this.groups[oldGroupName]) {
                this.groups[oldGroupName] = {};
            }
            this.groups[oldGroupName].newGroupName = newGroupName;
        },

        saveGroupName(oldGroupName) {
            const newGroupName = this.groups[oldGroupName]?.newGroupName;
            if (newGroupName && newGroupName !== oldGroupName) {
                this.renameGroup(oldGroupName, newGroupName);
            }
        },

        renameGroup(oldGroupName, newGroupName) {
            this.entity.renameAgentRelationGroup(oldGroupName, newGroupName).then(() => {
                const groupNames = Object.keys(this.groups);
                const newGroups = {};
                groupNames.forEach(groupName => {
                    if (groupName == oldGroupName) {
                        newGroups[newGroupName] = { ...this.groups[groupName], newGroupName: newGroupName };
                    } else {
                        newGroups[groupName] = this.groups[groupName];
                    }
                });

                this.groups = newGroups;
                this.reorderGroups();

                if (this.entity.registrationFilterConfig[oldGroupName]) {
                    this.entity.registrationFilterConfig[newGroupName] = this.entity.registrationFilterConfig[oldGroupName];
                    delete this.entity.registrationFilterConfig[oldGroupName]; 
                }
            });

            this.entity.agentRelations.forEach((relation) => {
                if(relation.group == oldGroupName) {
                    relation.group = newGroupName;
                }
            });
        },

        autoSave() {
            this.entity.submissionEvaluatorCount = this.localSubmissionEvaluatorCount;
            this.entity.save();
        },

        changeGroupFlag() {
            this.hasGroupsFlag = true;
        },

        changeMultipleEvaluators(value, group) {
            this.localSubmissionEvaluatorCount[group] = value ? 1 : null;
        },

        reorderGroups() {
            if (Object.keys(this.groups).length > 0) {
                const groupsKeys = Object.keys(this.groups);
                const indexMinervaVote = groupsKeys.indexOf(this.minervaGroup);
    
                if (indexMinervaVote != -1 && indexMinervaVote < groupsKeys.length - 1) {
                    groupsKeys.splice(indexMinervaVote, 1);
                    groupsKeys.push(this.minervaGroup);
                    
                    const newGroups = {};
                    groupsKeys.forEach(groupKey => {
                        newGroups[groupKey] = this.groups[groupKey];
                    });
                    
                    this.groups = {};
                    setTimeout(() => {
                        this.groups = {...newGroups};
                    });
                }
            }
        },

        enableRegisterFilterConf(value, group) {
            if (value) {
                if (!this.entity.registrationFilterConfig[group]) {
                    this.entity.registrationFilterConfig[group] = {};
                } 
            } else {
                delete this.entity.registrationFilterConfig[group];
            }

            this.autoSave();
        }
    },
});
