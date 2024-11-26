app.component('evaluation-qualification-detail', {
    template: $TEMPLATES['evaluation-qualification-detail'],

    props: {
        registration: {
            type: Entity,
            required: true
        }
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('evaluation-qualification-detail');
        return { text }
    },

    computed: {
        evaluationDetails() {
            return this.registration.evaluationsDetails ? this.registration.evaluationsDetails : $MAPAS.config.qualificationEvaluationDetail.data?.evaluationsDetails;
        }
    },

    methods: {
        formatResult(resultArray) {
            return resultArray
                .map(value => {
                    if (value === "valid") return this.text('Habilitado');
                    if (value === "invalid") return this.text('Inabilitado');
                    return value;
                })
                .join(", ");
        }
    }
});
