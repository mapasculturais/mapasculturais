app.component('opportunity-header', {
    template: $TEMPLATES['opportunity-header'],

    props: {
        opportunity: {
            type: Entity,
            required: true
        },
    },

    setup() {
        const text = Utils.getTexts('opportunity-header')
        return { text }
    },

    computed: {
        firstPhase() {
            return this.opportunity.parent || this.opportunity;
        },
        type() {
            switch (this.firstPhase.ownerEntity.__objectType) {
                case 'agent':
                    return this.text('Agente');
                case 'event':
                    return this.text('Evento');
                case 'space':
                    return this.text('Espaço');
                case 'project':
                    return this.text('Projeto');
            }
        },
    },
});
