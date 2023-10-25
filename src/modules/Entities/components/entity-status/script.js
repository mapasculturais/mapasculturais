app.component('entity-status', {
    template: $TEMPLATES['entity-status'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-status')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    mounted() { 
        this.updateMessage();
    },

    data() {
        return {
            classes: [],
            messageStr: '',
            showMessage: true,
            entityStatus : this.entity.status,
        };
    },

    watch: {
        entity: {
            handler(newEntity) {
                this.entityStatus = newEntity.status;
                this.updateMessage();
            },
            deep: true,
        }
    },

    computed: {
        message() {
            return this.messageStr;
        },

        entityType() {
            switch (this.entity.__objectType) {
                case 'opportunity':
                    return 'oportunidade';

                case 'agent':
                    return 'agente';

                case 'event':
                    return 'evento';

                case 'project':
                    return 'projeto';

                case 'space':
                    return 'espaço';

                default:
                    break;
            }
        }
    },

    methods: {
        updateMessage() {
            switch (this.entityStatus) {
                case 0:
                    if (this.entityType == 'oportunidade') {
                        this.messageStr = this.text('oportunidade em rascunho');
                        this.messageStr = this.messageStr.replace('%entity%', this.entityType);
                    } else {
                        this.messageStr = this.text('rascunho');
                        this.messageStr = this.messageStr.replace('%entity%', this.entityType);
                    }

                    break;

                case -2:
                    if (this.entityType == 'oportunidade') {
                        this.messageStr = this.text('oportunidade arquivada');
                        this.messageStr = this.messageStr.replace('%entity%', this.entityType);
                    } else {
                        this.messageStr = this.text('arquivado');
                        this.messageStr = this.messageStr.replace('%entity%', this.entityType);
                    }
                    break;


                case -10:
                    if (this.entityType == 'oportunidade') {
                        this.messageStr = this.text('oportunidade excluida');
                        this.messageStr = this.messageStr.replace('%entity%', this.entityType);
                    } else {

                        this.messageStr = this.text('excluido');
                        this.messageStr = this.messageStr.replace('%entity%', this.entityType);
                    }
                    break;

                default:
                    this.messageStr = '';
            }
            this.showMessage = this.messageStr ? true : false;
        }
    }
});
