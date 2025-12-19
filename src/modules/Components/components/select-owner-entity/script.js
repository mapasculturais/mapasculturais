app.component('select-owner-entity', {
    template: $TEMPLATES['select-owner-entity'],

    props: {
        entity: {
            type: Entity,
            required: true,
        },

        title: {
            type: String,
            required: true,
        },

        types: {
            type: Array,
            default: () => ['agent', 'event', 'space', 'project'],
        },
    },

    data () {
        return {
            entityTypeSelected: null,
        }
    },

    computed: {
        entityColorBorder() {
            switch(this.entity.ownerEntity.__objectType) {
                case 'project':
                    return 'project__border';
                case 'event':
                    return 'event__border';
                case 'space':
                    return 'space__border';
                case 'agent':
                    return 'agent__border--dark';
            }
        },

        entityColorClass() {
            switch(this.entity.ownerEntity.__objectType) {
                case 'project':
                    return 'project__color';
                case 'event':
                    return 'event__color';
                case 'space':
                    return 'space__color';
                case 'agent':
                    return 'agent__color--dark';
            }
        },

        entityType(){
            switch(this.entity.ownerEntity.__objectType) {
                case 'project':
                    return __('projeto', 'create-opportunity');
                case 'event':
                    return __('evento', 'create-opportunity');
                case 'space':
                    return __('espaço', 'create-opportunity');
                case 'agent':
                    return __('agente', 'create-opportunity');
            }
        },
    },

    methods: {
        getObjectTypeErrors() {
            return this.hasObjectTypeErrors() ? this.entity.__validationErrors?.objectType : [];
        },

        hasObjectTypeErrors() {
            return !this.entity.ownerEntity && this.entity.__validationErrors?.objectType;
        },

        resetEntity() {
            this.entity.ownerEntity = null;
            this.entityTypeSelected = null;
        },

        setEntity(Entity) {
            this.entity.ownerEntity = Entity;
        },        
    },
});
