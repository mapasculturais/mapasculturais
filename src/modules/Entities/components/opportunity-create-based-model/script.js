app.component('opportunity-create-based-model', {
    template: $TEMPLATES['opportunity-create-based-model'],
    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('opportunity-create-based-model')
        return { text, messages }
    },
    props: {
        entitydefault: {
            type: Entity,
            required: true,
        },
    },

    data() {
        let sendSuccess = false;
        let formData = {
            name: ''
        }

        return {
            fields: [],
            entity: null,
            entityTypeSelected: null,
            sendSuccess,
            formData
        }
    },
    computed: {
        areaClasses() {
            return this.areaErrors ? 'field error' : 'field';
        },
        
        modalTitle() {
            if (!this.entity?.id) {
                return __('criarOportunidade', 'opportunity-create-based-model');
            }
            if(this.entity.status==0){
                return __('oportunidadeCriada', 'opportunity-create-based-model');

            }
        },

        entityType(){
            switch(this.entity.ownerEntity.__objectType) {
                case 'project':
                    return __('projeto', 'opportunity-create-based-model');
                case 'event':
                    return __('evento', 'opportunity-create-based-model');
                case 'space':
                    return __('espaço', 'opportunity-create-based-model');
                case 'agent':
                    return __('agente', 'opportunity-create-based-model');
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
        async save() {
            const api = new API(this.entitydefault.__objectType);

            let objt = this.formData;
            objt.entityId = this.entitydefault.id;
            objt.objectType = this.entity?.ownerEntity?.__objectType;
            objt.ownerEntity = this.entity?.ownerEntity?._id;
            
            let error = null;

            if (error = this.validade(objt)) {
                let mess = "";
                mess = this.text('Todos os campos são obrigatórios.');
                this.messages.error(mess);
                return;
            }

            await api.POST(`/opportunity/generateopportunity/${objt.entityId}`, objt).then(response => response.json().then(dataReturn => {
                this.messages.success(this.text('Aguarde. Estamos gerando a oportunidade baseada no modelo.'), 6000);

                this.sendSuccess = true;

                setTimeout(() => {
                    window.location.href = `/gestao-de-oportunidade/${dataReturn.id}/#info`;
                }, "5000");                
            }));
        },
        validade(objt) {
            let result = null;
            let ignore = [];

            Object.keys(objt).forEach(function (item) {
                if (!objt[item] && !ignore.includes(item)) {
                    result = item;
                    return;
                }
            });
            return result;
        },
        handleSubmit(event) {
            event.preventDefault();
        },    

        createEntity() {
            this.entity = new Entity('opportunity');
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
