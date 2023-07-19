app.component('opportunity-owner-type', {
    template: $TEMPLATES['opportunity-owner-type'],

    setup() { },

    props: {
        entity: {
            type: Entity,
            required: true
        },

    },

    data() {
        return {

        }
    },

    computed: {},

    methods: {

        ownerType(entity) {
            let type = entity.ownerEntity.__objectType


            switch (type) {
                case 'agent':
                    return 'Agente'
                case 'space':
                    return 'Espaço'
                case 'event':
                    return 'Evento'
                case 'project':
                    return 'Projeto'
                case 'opportunity':
                    return 'Oportunidade'
            }

        },
    },
});