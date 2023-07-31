app.component('registration-related-space', {
    template: $TEMPLATES['registration-related-space'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },

    setup() {
        const text = Utils.getTexts('registration-related-space')
        return { text }
    },

    data() {
        return {
            opportunity: this.registration.opportunity,
        }
    },

    computed: {
        useSpaceRelation() {
            const metadata = 'useSpaceRelationIntituicao';
            if (this.opportunity[metadata] && this.opportunity[metadata] != 'dontUse') {
                return this.opportunity[metadata];
            }
            return 'dontUse';
        },

        relatedSpace() {
            return this.registration.spaceRelations[0];
        }
    },
    
    methods: {
        selectSpace(space) {
            this.registration.POST('createSpaceRelation', {data: {id: space.id}, callback: (relation) => {
                this.registration.spaceRelations[0] = relation;
                this.registration.relatedSpaces[0] = space;
                const messages = useMessages();
                messages.success(this.text('Espaço vinculado'));
            }});
        },
        removeSpace() {
            if (this.relatedSpace) {
                this.registration.POST('removeSpaceRelation', {data: {id: this.relatedSpace.space.id}, callback: () => {
                    let spaceRelations = this.registration.spaceRelations;
                    let relatedSpaces = this.registration.relatedSpaces;
                    if (spaceRelations) {
                        if (Object.keys(spaceRelations).length > 0) {
                            this.registration.spaceRelations =  {};
                        }
                    }        
                    if (relatedSpaces) {
                        if (Object.keys(relatedSpaces).length > 0) {
                            this.registration.relatedSpaces.length = 0;
                        }
                    }

                    const messages = useMessages();
                    messages.success(this.text('Espaço desvinculado'));
                }});
            }
        },
    },
});
