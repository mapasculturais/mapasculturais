app.component('create-agent' , {
    template: $TEMPLATES['create-agent'],
    emits: ['create'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('create-agent')
        return { text }
    },
    
    created() {
        this.iterationFields()
    },

    data() {
        return {
            entity: null,
            fields: [],
        }
    },

    props: {
        editable: {
            type: Boolean,
            default:true
        },
    },

    computed: {
        areaErrors() {
            return this.entity.__validationErrors['term-area'];
        },
        areaClasses() {
            return this.areaErrors ? 'field error' : 'field';
        },
        modalTitle() {
            if(this.entity?.id){
                return  __('agenteCriado', 'create-agent');
            } else {
                return  __('criarAgente', 'create-agent');
            }
        },
    },
    
    methods: {
        iterationFields() {
            let skip = [
                'createTimestamp', 
                'id',
                'location',
                'name', 
                'shortDescription', 
                'status', 
                'type',
                '_type', 
                'userId',
            ];
            Object.keys($DESCRIPTIONS.agent).forEach((item)=>{
                if(!skip.includes(item) && $DESCRIPTIONS.agent[item].required){
                    this.fields.push(item);
                }
            })
        },
        createEntity() {
            this.entity = Vue.ref(new Entity('agent'));
            this.entity.type = 1;
            this.entity.terms = {area: []}
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
        save (modal) {
            modal.loading(true);
            this.entity.save().then((response) => {
                this.$emit('create',response);
                modal.loading(false);

            }).catch((e) => {
                modal.loading(false);
            });
        },

        destroyEntity() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => this.entity = null, 200);
        }
    },
});
