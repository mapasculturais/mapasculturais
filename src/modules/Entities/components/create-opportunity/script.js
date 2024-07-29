app.component('create-opportunity', {
    template: $TEMPLATES['create-opportunity'],
    emits: ['create'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('create-opportunity')
        return { text }
    },

    data() {
        return {
            entity: null,
            fields: [],
            entityTypeSelected: null,
        }
    },

    props: {
        editable: {
            type: Boolean,
            default: true
        },
    },

    computed: {
        areaClasses() {
            return this.areaErrors ? 'field error' : 'field';
        },
        
        modalTitle() {
            if (!this.entity?.id) {
                return __('criarOportunidade', 'create-opportunity');
                console.log(this.entity.id);
            }
            if(this.entity.status==0){
                return __('oportunidadeCriada', 'create-opportunity');

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
    },

    methods: {
        handleSubmit(event) {
            event.preventDefault();
        },    

        createEntity() {
            this.entity = new Entity('opportunity');
            this.entity.type = 1;
            this.entity.terms = { area: [] }
        },

        createDraft(modal) {
            this.entity.status = 0;
            this.save(modal);
        },

        createPublic(modal) {
            //lançar dois eventos
            this.entity.status = 1;
            this.save(modal);
        },

        save(modal) {
            modal.loading(true);
            this.entity.save().then((response) => {
                this.$emit('create', response);
                modal.loading(false);
                Utils.pushEntityToList(this.entity);
            }).catch((e) => {
                modal.loading(false);
            });
        },

        setEntity(Entity) {
            this.entity.ownerEntity = Entity;
        },

        resetEntity() {
            this.entity.ownerEntity = null;
            this.entityTypeSelected = null;
        },

        destroyEntity() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => {
                this.entity = null;
                this.entityTypeSelected = null;
            }, 200);
        },

        hasObjectTypeErrors() {
            return !this.entity.ownerEntity && this.entity.__validationErrors?.objectType;
        },

        getObjectTypeErrors() {
            return this.hasObjectTypeErrors() ? this.entity.__validationErrors?.objectType : [];
        },
    },
});
