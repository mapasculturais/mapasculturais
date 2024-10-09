app.component('evaluation-documentary-datail', {
    template: $TEMPLATES['evaluation-documentary-datail'],

    props: {
        registration: {
            type: Entity,
            required: true
        }
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('evaluation-documentary-datail');
        return { text }
    },

    data() {
        return {}
    },

    computed: {},

    methods: {
        statusColor(data) {
            switch (data) {
                case "1" :
                case 'valid' :
                    return 'is-success';

                case "-1" :
                case 'invalid' :

                    return 'is-danger';
                default:
                    return 'is-danger';
            }
        },
        statusString(data) {
            switch (data) {
                case "1" :
                case 'valid' :
                    return this.text('valido');

                case "-1" :
                case 'invalid' :

                    return this.text('invalido');
                default:
                    return this.text('invalido');
            }
        }
    },
});
