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
           minervaGroup: 'Comissão de voto final'
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
        initializeGroups() {
            let groups = {};

            if (!groups['Comissão de avaliação'] && Object.keys(this.groups).length === 0) {
                groups['Comissão de avaliação'] = [];
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
            
            Object.keys(this.groups || {}).forEach(groupName => {
                if(this.entity.submissionEvaluatorCount[groupName] && !this.localSubmissionEvaluatorCount[groupName]) {
                    this.localSubmissionEvaluatorCount[groupName] = this.entity.submissionEvaluatorCount[groupName];
                }

                if (!this.entity.submissionEvaluatorCount[groupName] && !this.localSubmissionEvaluatorCount[groupName]) {
                    this.localSubmissionEvaluatorCount[groupName] = null;
                } 
            });

            this.entity.submissionEvaluatorCount = this.localSubmissionEvaluatorCount;

            this.entity.disableMessages();
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

            this.reorderGroups();
        },

        removeGroup(group) {
            delete this.groups[group]
            delete this.localSubmissionEvaluatorCount[group];
            this.entity.removeAgentRelationGroup(group);

            this.autoSave();
        },

        updateGroupName(oldGroupName, newGroupName) {
            if (newGroupName && newGroupName !== oldGroupName) {
                this.groups[oldGroupName].newGroupName = newGroupName;
            }
        },

        saveGroupName(oldGroupName) {
            const newGroupName = this.groups[oldGroupName].newGroupName;
            if (newGroupName && newGroupName !== oldGroupName) {
                this.renameGroup(oldGroupName, newGroupName);
            }
        },

        renameGroup(oldName, newName) {
            this.entity.renameAgentRelationGroup(oldName, newName).then(() => {
                const groupNames = Object.keys(this.groups);
                const newGroups = {};
                groupNames.forEach(groupName => {
                    if (groupName == oldName) {
                        newGroups[newName] = { ...this.groups[groupName], newGroupName: newName };
                    } else {
                        newGroups[groupName] = this.groups[groupName];
                    }
                });

                this.groups = newGroups;
                this.reorderGroups();
            });

            this.entity.agentRelations.map((relation) => {
                if(relation.group == oldName) {
                    relation.group = newName;
                }
            });
        },

        autoSave() {
            this.entity.submissionEvaluatorCount = this.localSubmissionEvaluatorCount;
            this.entity.save();
            this.entity.enableMessages();
        },

        changeGroupFlag() {
            this.hasGroupsFlag = true;
        },

        changeMultipleEvaluators(event, group) {
            this.localSubmissionEvaluatorCount[group] = (!event.target.checked) ? null : 1;
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
        }
    },
});
