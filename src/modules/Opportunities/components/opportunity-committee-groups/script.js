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
            editGroupName: false,
            newName: '',
            localValuersPerRegistration: {},
            tabSelected: '',
            hasGroupsFlag: false,
            minervaGroup: '@tiebreaker',
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
            const filteredGroups = Object.keys(this.entity.relatedAgents).filter(group => group !== this.minervaGroup);
            const groupCount = filteredGroups.length;
            return groupCount >= 2 && !this.entity.relatedAgents[this.minervaGroup];
        }
    },

    mounted() {
        this.initializeGroups();
        this.initiliazeValuersPerRegistration();
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

            if (!groups['Comissão de avaliação'] && Object.keys(this.entity.relatedAgents).length === 0) {
                groups['Comissão de avaliação'] = {};
            }
            
            for (let groupName of Object.keys(this.entity.relatedAgents)) {
                if (groupName !== "group-admin" && groupName !== '@support') {
                    groups[groupName] = this.entity.relatedAgents[groupName];
                }
            }
            this.entity.relatedAgents = groups;
        },

        initiliazeValuersPerRegistration() {
            if(!this.entity?.valuersPerRegistration) {
                this.entity.valuersPerRegistration = {};
            }
            
            // Se não existir configuração de filtro para avaliadores, cria objeto vazio 
            if(!this.entity?.fetchFields) {
                this.entity.fetchFields = {};
            }
            
            Object.keys(this.entity.relatedAgents || {}).forEach(groupName => {
                if(this.entity.valuersPerRegistration[groupName] && !this.localValuersPerRegistration[groupName]) {
                    this.localValuersPerRegistration[groupName] = this.entity.valuersPerRegistration[groupName];
                }

                if (!this.entity.valuersPerRegistration[groupName] && !this.localValuersPerRegistration[groupName]) {
                    this.localValuersPerRegistration[groupName] = null;
                }
            });

            this.entity.valuersPerRegistration = this.localValuersPerRegistration;

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

            this.entity.relatedAgents = { ...this.entity.relatedAgents, [group]: this.entity.agentRelations[group] };

            this.localValuersPerRegistration[group] = null;

            if(!this.entity?.fetchFields) {
                this.entity.fetchFields = {}
            }

            if(!this.entity?.fetchFields[group]) {
                this.entity.fetchFields[group] = {}
            }

            this.reorderGroups();
        },

        removeGroup(group) {
            delete this.entity.relatedAgents[group]
            delete this.localValuersPerRegistration[group];
            delete this.entity.fetchFields[group];
            this.entity.removeAgentRelationGroup(group);

            this.autoSave();
        },

        updateGroupName(oldGroupName, newGroupName) {
            if (!this.entity.relatedAgents[oldGroupName]) {
                this.entity.relatedAgents[oldGroupName] = {};
            }
            this.entity.relatedAgents[oldGroupName].newGroupName = newGroupName;
        },

        saveGroupName(oldGroupName) {
            const newGroupName = this.entity.relatedAgents[oldGroupName]?.newGroupName;
            if (newGroupName && newGroupName !== oldGroupName) {
                this.renameGroup(oldGroupName, newGroupName);
            }
        },

        renameGroup(oldGroupName, newGroupName) {
            this.entity.renameAgentRelationGroup(oldGroupName, newGroupName).then(() => {
                const groupNames = Object.keys(this.entity.relatedAgents);
                const newGroups = {};
                groupNames.forEach(groupName => {
                    if (groupName == oldGroupName) {
                        newGroups[newGroupName] = { ...this.entity.relatedAgents[groupName], newGroupName: newGroupName };
                    } else {
                        newGroups[groupName] = this.entity.relatedAgents[groupName];
                    }
                });

                this.entity.relatedAgents = newGroups;
                this.reorderGroups();

                if (this.entity.fetchFields[oldGroupName]) {
                    this.entity.fetchFields[newGroupName] = this.entity.fetchFields[oldGroupName];
                    delete this.entity.fetchFields[oldGroupName]; 
                }
            });

            this.entity.agentRelations.forEach((relation) => {
                if(relation.group == oldGroupName) {
                    relation.group = newGroupName;
                }
            });
        },

        autoSave() {
            this.entity.valuersPerRegistration = this.localValuersPerRegistration;
            this.entity.save();
        },

        changeGroupFlag() {
            this.hasGroupsFlag = true;
        },

        changeMultipleEvaluators(value, group) {
            this.localValuersPerRegistration[group] = value ? 1 : null;
        },

        reorderGroups() {
            if (Object.keys(this.entity.relatedAgents).length > 0) {
                const groupsKeys = Object.keys(this.entity.relatedAgents);
                const indexMinervaVote = groupsKeys.indexOf(this.minervaGroup);
    
                if (indexMinervaVote != -1 && indexMinervaVote < groupsKeys.length - 1) {
                    groupsKeys.splice(indexMinervaVote, 1);
                    groupsKeys.push(this.minervaGroup);
                    
                    const newGroups = {};
                    groupsKeys.forEach(groupKey => {
                        newGroups[groupKey] = this.entity.relatedAgents[groupKey];
                    });
                    
                    this.entity.relatedAgents = {};
                    setTimeout(() => {
                        this.entity.relatedAgents = {...newGroups};
                    });
                }
            }
        },

        enableRegisterFilterConf(value, group) {
            if (value) {
                if (!this.entity.fetchFields[group]) {
                    this.entity.fetchFields[group] = {};
                } 
            } else {
                delete this.entity.fetchFields[group];
            }

            this.autoSave();
        },

        renameTab(event, slug) {
            const tab = this.$refs.tabs.tabs.find(tab => {
                return tab.slug == slug;
            })
            tab.label = event.target.value;
        },
    },
});
