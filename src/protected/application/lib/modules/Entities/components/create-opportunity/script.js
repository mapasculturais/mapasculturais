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
        }
    },

    props: {
        editable: {
            type: Boolean,
            default: true
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
            if (this.entity?.id) {
                if (this.entity.status == 1) {
                    return __('oportunidadeCriada', 'create-opportunity');
                } else {
                    return __('criarRascunho', 'create-opportunity');
                }
            } else {
                return __('criarOportunidade', 'create-opportunity');

            }
        },
    },

    methods: {
        createEntity() {

            this.entity = new Entity('opportunity');
            this.entity.type = 1;
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
