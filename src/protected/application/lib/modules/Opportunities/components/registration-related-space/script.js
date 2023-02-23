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
        selectAgent() {
        },
        removeAgent() {
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
