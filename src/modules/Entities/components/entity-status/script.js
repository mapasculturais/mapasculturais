app.component('entity-status', {
    template: $TEMPLATES['entity-status'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-status')
        return { text }
    },
    created() {

    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },
    data() {
        let message = '';
        let showMessage = true;

        switch (this.entity.status) {
            case 0:
                if (this.verifyType() == 'oportunidade') {
                    message = this.text('oportunidade em rascunho');
                    message = message.replace('%entity%', this.verifyType());
                } else {
                    message = this.text('rascunho');
                    message = message.replace('%entity%', this.verifyType());
                }

                break;

            case -2:
                if (this.verifyType() == 'oportunidade') {
                    message = this.text('oportunidade arquivada');
                    message = message.replace('%entity%', this.verifyType());
                } else {

                    message = this.text('arquivado');
                    message = message.replace('%entity%', this.verifyType());
                }
                break;


            case -10:
                if (this.verifyType() == 'oportunidade') {
                    message = this.text('oportunidade excluida');
                    message = message.replace('%entity%', this.verifyType());
                } else {

                    message = this.text('excluido');
                    message = message.replace('%entity%', this.verifyType());
                }
                break;


            default:
                showMessage = false;
                break;
        }
        return {
            classes: [],
            type: 'warning',
            message,
            showMessage,
        };

    },


    methods: {
        // renderMessage(entity) {
        //     let message = '';
        //     switch (entity.status) {
        //         case 0:
        //             message = this.text('rascunho');
        //             message = message.replace('%entity%', this.verifyType(entity));

        //             return message;

        //         case -2:
        //             message = this.text('arquivado');
        //             message = message.replace('%entity%', this.verifyType(entity));
        //             return message;


        //         case -10:
        //             message = this.text('excluido');
        //             message = message.replace('%entity%', this.verifyType(entity));
        //             return message;


        //         default:
        //             break;
        //     }
        // },
        verifyType() {
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
});
