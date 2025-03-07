app.component('panel--evaluations-tabs', {
    template: $TEMPLATES['panel--evaluations-tabs'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('panel--evaluations-tabs')
        return { text, hasSlot }
    },

    props: {
        isReportingPhase: {
            type: Boolean,
            default: false
        },
    },

    data() {
        let query = {
            '@permissions': 'viewEvaluations',
            'status': 'IN(1,-1)',
            'isReportingPhase': this.isReportingPhase ? `EQ(1)` : 'OR(EQ(0),NULL())',
        };

        return {
            query,
        }
    },
});
