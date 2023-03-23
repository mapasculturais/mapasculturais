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
        type() {
            console.log(this.opportunity.ownerEntity.__objectType);
            switch (this.opportunity.ownerEntity.__objectType) {
                case 'Opportunity':
                    return this.text('Oportunidade');
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

    methods: {
    }

});
