app.component('mc-summary-agent-info', {
    template: $TEMPLATES['mc-summary-agent-info'],

    props: {
        entity:{
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },
    data() {
        let avaliableEvaluationFields = $MAPAS.avaliableEvaluationFields
        let opportunity = this.entity.opportunity;
        let owner = this.entity.agentsData.owner;
        let colective = this.entity.agentsData?.coletivo;
        let institution = this.entity.agentsData?.instituicao;

        return { avaliableEvaluationFields, owner, colective, institution, opportunity }
    },
    methods: {
        canSee(item) {
            if(this.entity.currentUserPermissions['@control']){
                return true
            }

            if (this.avaliableEvaluationFields[item]) {
                return true;
            }
            
            return false;
        },
        getAvatarRelatedEntity(type) {
            var avatar = null;
            if (this.entity.agentRelations && this.entity.agentRelations.hasOwnProperty(type)) {
                this.entity.agentRelations[type].forEach(element => {
                    var id = this.entity.agentsData[type].id;
                    if (id == element.agent.id) {
                        if (element.agent?.files?.avatar) {
                            avatar = element.agent.files.avatar.url
                        }
                    }
                });
            }

            return avatar;
        }
    },
});
