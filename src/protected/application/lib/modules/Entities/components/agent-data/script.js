app.component('agent-data', {
    template: $TEMPLATES['agent-data'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-owner')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: __('Dados Pessoais', 'agent-data')
        },

        classes: {
            type: [String, Array, Object],
            required: false
        },
        secondTitle: {
            type: String,
            default: __('Dados Pessoais Sensíveis', 'agent-data')
        },
    },
    methods: {
        verifyEntity() {
            if (this.entity.dataDeNascimento != null || this.entity.genero?.length > 0 || this.entity.orientacaoSexual?.length > 0 ||
                this.entity.agenteItinerante?.length > 0 || this.entity.raca?.length > 0 || this.entity.escolaridade?.length > 0 ||
                this.entity.pessoaDeficiente?.length > 0 || this.entity.comunidadesTradicional?.length > 0 || this.entity.comunidadesTradicionalOutros?.length > 0) {
                return true;
            }
            else {
                return false;
            }
        },
        createDate(dataDeNascimento) {
            const data = new Date(dataDeNascimento._date);
            const dataFormatada = `${data.getDate()}/${data.getMonth() + 1}/${data.getFullYear()}`;
            return dataFormatada;
        },
    },
});
