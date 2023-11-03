app.component('panel--evaluations-tabs', {
    template: $TEMPLATES['panel--evaluations-tabs'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('panel--evaluations-tabs')
        return { text, hasSlot }
    },

    data() {
        let query = {
            '@permissions': 'viewEvaluations',
            'status': 'IN(1,-1)',
        };

        return {
            query,
        }
    },
});
