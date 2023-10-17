app.component('link-opportunity', {
    template: $TEMPLATES['link-opportunity'],
    // emits: ['create'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('link-opportunity')
        return { text }
    },

    data() {
        return {
            entityTypeSelected: this.entity.ownerEntity.__objectType,
            fields: [],
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: true
        },
    },

    computed: {
        
        entityType(){
            switch(this.entity.ownerEntity.__objectType) {
                case 'project':
                    return __('projeto', 'link-opportunity');
                case 'event':
                    return __('evento', 'link-opportunity');
                case 'space':
                    return __('espaço', 'link-opportunity');
                case 'agent':
                    return __('agente', 'link-opportunity');
            }
        },
        entityColorClass() {
            let type = this.entity.ownerEntity.__objectType;
            return type+'__color'+(type=='agent' ? '--dark':'');
        },
        entityColorBorder() {
            let type = this.entity.ownerEntity.__objectType;
            return type+'__border'+(type=='agent' ? '--dark':'');
        },
    },

    methods: {
        setEntity(Entity) {
            this.entity.ownerEntity = Entity;
        },

        resetEntity() {
            console.log(this.entityTypeSelected);
            // this.entity.ownerEntity = null;
            // this.entityTypeSelected = type;
        },

        hasObjectTypeErrors() {
            return !this.entity.ownerEntity && this.entity.__validationErrors?.objectType;
        },

        getObjectTypeErrors() {
            return this.hasObjectTypeErrors() ? this.entity.__validationErrors?.objectType : [];
        },
    },
});
