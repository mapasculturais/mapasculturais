app.component('create-app' , {
    template: $TEMPLATES['create-app'],
    emits: ['create'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('create-app')
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
       
    },

    computed: {
        areaErrors() {
            // return this.entity.__validationErrors['term-area'];
        },
        areaClasses() {
            // return this.areaErrors ? 'field error' : 'field';
        },
        modalTitle() {
            if(this.entity?.id){
                if(this.entity.status==1){
                    return  __('appCriado', 'create-app');
                }else {
                    return  __('criarRascunho', 'create-app');
                }
            }else {
                return  __('criarApp', 'create-app');

            }
        },
    },
    
    methods: {
        iterationFields() {
            let skip = [
                'createTimestamp', 
                'id',
                'name', 
                'status', 
                'userId',
                'publicKey',
                'privateKey',
            ];
            Object.keys($DESCRIPTIONS.app).forEach((item)=>{
                if(!skip.includes(item) && $DESCRIPTIONS.app[item].required){
                    this.fields.push(item);
                }
            })
        },
        createEntity() {
            this.entity = Vue.ref(new Entity('app'));
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
