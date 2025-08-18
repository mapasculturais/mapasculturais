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
        showSectionAndCriterion(type) {
            if (type?.categories && type.categories.length > 0 && !type.categories.includes(this.registration.category)) {
                return false
            }

            return true;
        },
        formatResult(resultArray) {
            return resultArray
                .map(value => {
                    if (value === "valid") return this.text('Atende');
                    if (value === "invalid") return this.text('Não atende');
                    if (value === 'others') return undefined;
                    return value;
                })
                .join("\n - ");
        },
        formatConsolidatedResult(result) {
            if (result === 'valid') return this.text('Habilitado');
            if (result === 'invalid') return this.text('Inabilitado');
            return result;
        }
    }
});
