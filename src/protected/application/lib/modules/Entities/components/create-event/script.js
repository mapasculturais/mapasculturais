app.component('create-event', {
    template: $TEMPLATES['create-event'],
    emits: ['create'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('create-event')
        return { text }
    },

    created() {
        this.iterationFields();
        var stat = 'publish';
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
        linguagemErrors() {
            return this.entity.__validationErrors['term-linguagem'];
        },
        linguagemClasses() {
            return this.linguagemErrors ? 'field error' : 'field';
        },
        modalTitle() {
            if (this.entity?.id) {
                if (this.entity.status == 1) {
                    return __('eventoCriado', 'create-event');
                } else {
                    return __('criarRascunho', 'create-event');
                }
            } else {
                return __('criarEvento', 'create-event');

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
            Object.keys($DESCRIPTIONS.event).forEach((item) => {
                if (!skip.includes(item) && $DESCRIPTIONS.event[item].required) {
                    this.fields.push(item);
                }
            })
        },
        createEntity() {
            this.entity = Vue.ref(new Entity('event'));
            this.entity.type = 1;
            this.entity.terms = { linguagem: [] }

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
                console.log(response);
                this.$emit('create', response)
                modal.loading(false);
                Utils.pushEntityToList(this.entity);
            }).catch((e) => {
                modal.loading(false);
            });
        },


        destroyEntity() {
            setTimeout(() => this.entity = null, 200);
        },
    },
});
