app.component('registration-related-space', {
    template: $TEMPLATES['registration-related-space'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },

    data() {
        return {
            opportunity: this.registration.opportunity,
        }
    },

    computed: {
        spaceRelation() {
            const relations = [];

            for (let relation of $MAPAS.config.registrationRelatedSpace) {

                const metadata = 'useSpaceRelationIntituicao';                
                if (this.opportunity[metadata] != 'dontUse') {
                    if (this.opportunity[metadata] == 'required') {
                        relation.required = true;
                    }
                    relations.push(relation);
                }
            }

            return relations;            
        },  
    },
    
    methods: {
        selectSpace(space) {            
            const api = this.registration.API;
            
            api.POST(this.registration.getUrl('createSpaceRelation'), {id: space.id}).then((response) => {
                const messages = useMessages();
                /**
                 * @todo criar arquivo de traduções
                 */
                messages.success('Espaço vinculado.');
            });
        },
        removeSpace() {
            let spaceRelations = this.registration.agentRelations[relation.agentRelationGroupName];
            let relatedSpaces = this.registration.relatedAgents[relation.agentRelationGroupName];

            if (spaceRelations) {
                if (Object.keys(spaceRelations).length > 0) {
                    spaceRelations.pop();
                }
            }

            if (relatedSpaces) {
                if (Object.keys(relatedSpaces).length > 0) {
                    relatedSpaces.pop();
                }
            }
        },
        hasRelations(relation) {
            console.log('relation: ', relation);
            if (relation) {
                if (Object.keys(relation).length > 0) {
                    return true;
                }
            }
            return false;
        }
    },
});
