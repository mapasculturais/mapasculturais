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
           groups: {},
           editGroupName: false,
           newName: '',
           localSubmissionEvaluatorCount: {},
           tabSelected: '',
           hasGroupsFlag: false,
           hasTwoOrMoreGroups: false,
           minervaGroup: 'Grupo de Voto Final'
        }
    },

    mounted() {
        this.initializeGroups();
        this.initiliazeSubmissionEvaluatorCount();
    },
    
    methods: {
        initializeGroups() {
            let groups = {};
            for (let relation of this.entity.agentRelations) {
                let groupName = relation.newGroupName ?? relation.group;
                
                if (groupName !== "group-admin" && groupName !== '@support') {
                    groups[groupName] = relation;
                }
            }
        
            this.groups = groups;
            this.hasGroupsFlag = Object.keys(this.groups).length > 0;
            this.checkGroupCount();
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
                    this.localSubmissionEvaluatorCount[groupName] = 1;
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
            this.localSubmissionEvaluatorCount[group] = 1;
            this.checkGroupCount();

            if(disableMinervaGroup) {
                this.hasTwoOrMoreGroups = false;
            }
        },

        removeGroup(group) {
            delete this.groups[group]
            this.checkGroupCount();

            delete this.entity.submissionEvaluatorCount[group];
            this.entity.removeAgentRelationGroup(group);
            this.entity.enableMessages();
        },

        renameGroup(oldName, newName, popover) {
            this.entity.renameAgentRelationGroup(oldName, newName).then(() => {
                if (popover) popover.close();
            });

            this.entity.agentRelations.map((relation) => {
                if(relation.group == oldName) {
                    relation.group = newName;
                }
            });

            this.initializeGroups();
        },

        autoSave() {
            this.entity.submissionEvaluatorCount = this.localSubmissionEvaluatorCount;
            this.entity.save();
            this.entity.enableMessages();
        },

        changeGroupFlag() {
            this.hasGroupsFlag = true;
        },

        checkGroupCount() {
            const filteredGroups = Object.keys(this.groups).filter(group => group !== this.minervaGroup);
            const groupCount = filteredGroups.length;
            this.hasTwoOrMoreGroups = groupCount >= 1 && !this.groups[this.minervaGroup];
        }
    },
});
