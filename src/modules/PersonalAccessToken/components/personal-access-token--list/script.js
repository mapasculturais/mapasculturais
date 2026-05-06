app.component('personal-access-token--list', {
    template: $TEMPLATES['personal-access-token--list'],

    setup() {
        const text = Utils.getTexts('personal-access-token--list');
        return { text };
    },
});
