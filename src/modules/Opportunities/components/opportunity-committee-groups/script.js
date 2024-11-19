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
            tabSelected: '',
            hasGroupsFlag: false,
            minervaGroup: '@tiebreaker',
            globalExcludeFields: [],
            individualExcludeFields: [],
            relatedAgentsIndex: Object.keys(this.entity.relatedAgents),
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
        this.initiliazeProperties();

        this.$nextTick(() => {
            if (!this.$refs.tabs.activeTab) {
                this.$refs.tabs.activeTab = this.$refs.tabs.tabs[0];
            }
        });
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

        initiliazeProperties() {
            const props = ['fetchFields', 'valuersPerRegistration', 'ignoreStartedEvaluations'];

            for (let group of props) {
                if (!this.entity[group] || this.entity[group] instanceof Array) {
                    this.entity[group] = {};
                }
            }
        },

        hasGroups() {
            return this.hasGroupsFlag;
        },

        groupCreation(event, popover) {
            event.preventDefault();
            this.addGroup(this.newGroupName);
            popover.close();

            this.$nextTick(() => {
                this.$refs.tabs.tabs.forEach(tab => {
                    if (tab.label == this.newGroupName) {
                        this.$refs.tabs.activeTab = tab;
                        this.newGroupName = '';
                    }
                });
            });
        },

        cancelGroupCreation(popover) {
            popover.close()
            this.newGroupName = '';
        },

        addGroup(group, disableMinervaGroup = false) {
            if (!this.entity.relatedAgents[group]) {
                this.entity.relatedAgents[group] = [];
            }

            if (!this.entity.agentRelations[group]) {
                this.entity.agentRelations[group] = [];
            }

            this.entity.relatedAgents = { ...this.entity.relatedAgents, [group]: this.entity.agentRelations[group] };

            if(!this.entity?.fetchFields) {
                this.entity.fetchFields = {}
            }

            this.reorderGroups();
        },

        async removeGroup(group) {
            const agentRelations = this.entity.agentRelations;

            if(agentRelations && agentRelations.length > 0) {
                const reviewersToRemove = agentRelations.filter(relation => relation.group === group);
                reviewersToRemove.forEach(relation => {
                    const userId = relation.agentUserId;
                    let userGroups = this.entity.agentRelations.filter(relation => relation.agentUserId === userId);

                    if (userGroups.length <= 1) {
                        this.delReviewerData(userId);
                    }
                });
            }

            delete this.entity.relatedAgents[group]
            delete this.entity.valuersPerRegistration[group];
            delete this.entity.fetchFields[group];
            this.entity.removeAgentRelationGroup(group);

            await this.autoSave();

            this.$nextTick(() => {
                const firstTab = this.$refs.tabs.tabs[0];
        
                if (firstTab) {
                    this.$refs.tabs.activeTab = firstTab;
                }

            })
            
        },

        delReviewerData(userId) {
            const properties = [
                'fetch',
                'fetchSelectionFields',
                'fetchRanges',
                'fetchProponentTypes',
                'fetchCategories'
            ];

            properties.forEach(property => {
                if (this.entity[property]) {
                    if (this.entity[property][userId]) {
                        delete this.entity[property][userId];
                    }
                }
            });
        },

        autoSave() {
            const entity = this.entity;
            return entity.save();
        },

        changeGroupFlag() {
            this.hasGroupsFlag = true;
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

        enableValuersPerRegistration(value, group) {
            if (value) {
                if (!this.entity.valuersPerRegistration[group]) {
                    this.entity.valuersPerRegistration[group] = 1;
                } 
            } else {
                delete this.entity.valuersPerRegistration[group];
            }

            this.autoSave();
        },

        enableIgnoreStartedEvaluations(value, group) {
            if (value) {
                if (!this.entity.ignoreStartedEvaluations[group]) {
                    this.entity.ignoreStartedEvaluations[group] = true;
                } 
            } else {
                delete this.entity.ignoreStartedEvaluations[group];
            }

            this.autoSave();
        },

        renameTab(event, index, oldName) {
            let newName = event.target.value;
            this.$refs.tabs.tabs[index].label = newName;
            this.entity.renameAgentRelationGroup(oldName, newName);
            this.reorderGroups();

            this.$nextTick(() => {
                const lastTab = this.$refs.tabs.tabs[this.$refs.tabs.tabs.length - 1];

                if (lastTab) {
                    this.$refs.tabs.activeTab = lastTab;
                }
            });
        },

        enableExternalReviews(value) {
            this.entity.showExternalReviews = value ? true : false;
            this.autoSave();
        }
    },
});
