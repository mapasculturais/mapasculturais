app.component('link-opportunity', {
    template: $TEMPLATES['link-opportunity'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('link-opportunity')
        return { text }
    },

    data() {
        return {
            entityTypeSelected: this.entity.ownerEntity.__objectType,
            fields: [],
            placeholder: this.entity.ownerEntity.__objectType, 
            selected: true,
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

        entityType() {
            switch (this.entity.ownerEntity.__objectType) {
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
    },

    methods: {
        setSelected() {
            this.selected = false;
        },
        
        verifySelected(entityTypeSelected) {
            let selected = '';
            switch (entityTypeSelected) {
                case 'project':
                    selected = 'projetos';
                    return selected;
                case 'event':
                    selected = 'eventos';
                    return selected;
                case 'space':
                    selected = 'espaços';
                    return selected;
                case 'agent':
                    selected = 'agentes';
                    return selected;
            }
        },
        setEntity(Entity) {
            this.entity.ownerEntity = Entity;
            this.entity.save(200);
        },
        toggleSelected() {
            this.selected = !this.selected;
        },
        resetEntity() {
            this.entity.ownerEntity = null;
            this.entityTypeSelected = null;
        },

        hasObjectTypeErrors() {
            return !this.entity.ownerEntity && this.entity.__validationErrors?.objectType;
        },

        getObjectTypeErrors() {
            return this.hasObjectTypeErrors() ? this.entity.__validationErrors?.objectType : [];
        },
    },
});
