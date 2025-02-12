app.component('panel--entity-models-card', {
    template: $TEMPLATES['panel--entity-models-card'],
    emits: ['deleted'],
    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('panel--entity-models-card')
        return { text, messages }
    },
    props: {
        class: {
            type: [String, Array, Object],
            default: ''
        },
        entity: {
            type: Entity,
            required: true
        },

        onDeleteRemoveFromLists: {
            type: Boolean,
            default: true
        }
    },
    data() {        
        const api = new API(this.entity.__objectType);
        const response = api.GET('/opportunity/findOpportunitiesModels');
        response.then((r) => r.json().then((r) => {
           this.models = r;
        }));

        let isModelPublic = this.entity.isModelPublic == 1 ? true : false;


        const MODEL_OFFICIAL = 'MODELO OFICIAL';
        const MODEL_PRIVATE = 'MODELO PRIVADO';
        const MODEL_PUBLIC = 'MODELO PÚBLICO';

        const typeModels = {
            MODEL_OFFICIAL,
            MODEL_PRIVATE,
            MODEL_PUBLIC
        };

        return {
            isModelPublic,
            models: [],
            typeModels
        }
    },

    watch: {
        'isModelPublic'(_new,_old){
            if(_new != _old){
                this.isActive(_new);
            }
        },
    },
    methods: {
        isActive(active) {
            this.entity.isModelPublic = active ? 1 : 0;
            this.modelPublic();
        },
        async modelPublic(){
            const api = new API(this.entity.__objectType);
            let objt = {
                isModelPublic: this.entity.isModelPublic
            };

            await api.POST(`/opportunity/modelpublic/${this.entity.id}`, objt).then(res => {
                this.messages.success(this.text('Modelo atualizado com sucesso'));
            });
        }
    },
    computed: {
        classes() {
            return this.class;
        }, 
        leftButtons() {
            return 'delete';
        },
        showModel() {
            let showModel = false;
            if (this.entity.owner._id == $MAPAS.user.profile._id || this.entity.isModelPublic) {
                showModel = true;
            }

            return showModel;
        },
        getTypeModel() {

            let model_ = this.models.filter((model) => {
                return this.entity.id == model.id;
            });

            if (model_[0] != 'undefined') {
                if (model_[0]?.modelIsOfficial) {
                    return this.typeModels.MODEL_OFFICIAL;
                }
            }          

            return this.entity.isModelPublic == 0 ? this.typeModels.MODEL_PRIVATE : this.typeModels.MODEL_PUBLIC;
        },
    }
})
