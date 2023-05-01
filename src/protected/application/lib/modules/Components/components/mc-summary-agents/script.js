app.component('mc-summary-agents', {
    template: $TEMPLATES['mc-summary-agents'],

    setup() {
    },
    mounted() {
        return {}
    },
    props: {},

    data() {
        let entity = $MAPAS.requestedEntity;
        let avaliableEvaluationFields = $MAPAS.avaliableEvaluationFields
        let opportunity = $MAPAS.opportunity;
        let owner = entity.agentsData.owner;
        let colective = entity.agentsData?.coletivo;
        let institution = entity.agentsData?.instituicao;

        return { entity, avaliableEvaluationFields, owner, colective, institution, opportunity }
    },

    computed: {},

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
