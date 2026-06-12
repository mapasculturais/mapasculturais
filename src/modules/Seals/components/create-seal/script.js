app.component('create-seal' , {
    template: $TEMPLATES['create-seal'],
    emits: ['create'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('create-seal')
        return { text }
    },

    watch: {
        requirePeriod: {
            handler(item) {
                if(item) {
                    this.entity.validPeriod = null;
                } else {
                    this.entity.validPeriod = 0;
                }
            }
        }
    },

    created() {
        this.iterationFields()
    },

    data() {
        return {
            entity: null,
            fields: [],
            requirePeriod: null,
            pendingSensitive: null,
            dialogUid: 'create-seal-sensitive-' + Utils.uid(),
        }
    },

    props: {

    },

    computed: {
        modalTitle() {
            if(this.entity?.id){
                if(this.entity.status == 1){
                    return  __('seloCriado', 'create-seal');
                }else {
                    return  __('criarRascunho', 'create-seal');
                }
            }else {
                return  __('criarSelo', 'create-seal');

            }
        },
        sensitiveLabel() {
            return this.entity?.$PROPERTIES?.sensitive?.label || this.text('sensitiveDefaultLabel');
        },
    },

    methods: {
        iterationFields() {
            let skip = [
                'createTimestamp',
                'id',
                'name',
                'shortDescription',
                'validPeriod',
                'status',
                '_ownerId',
            ];
            Object.keys($DESCRIPTIONS.seal).forEach((item)=>{
                if(!skip.includes(item) && $DESCRIPTIONS.seal[item].required){
                    this.fields.push(item);
                }
            });

            if (!this.fields.includes('sensitive')) {
                this.fields.push('sensitive');
            }
        },
        createEntity() {
            this.entity = Vue.ref(new Entity('seal'));
            this.entity.type = 1;
            this.entity.validPeriod = 0;
            this.pendingSensitive = null;
            // this.entity.terms = {area: []}
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
        },

        onSensitiveChange(event, openConfirm) {
            const checked = event.target.checked;
            const currentValue = !!this.entity.sensitive;

            if (checked === currentValue) {
                return;
            }

            this.pendingSensitive = checked;
            event.target.checked = currentValue;
            openConfirm();
        },

        confirmSensitive() {
            if (this.pendingSensitive !== null) {
                this.entity.sensitive = this.pendingSensitive;
                this.pendingSensitive = null;
            }
        },

        cancelSensitive() {
            this.pendingSensitive = null;
        },

        sensitiveConfirmMessage() {
            if (this.pendingSensitive) {
                return this.text('confirmSensitiveOn');
            }
            return this.text('confirmSensitiveOff');
        },
    },
});
