app.component('main-footer', {
    template: $TEMPLATES['main-footer'],

    setup() { 
        const text = Utils.getTexts('main-footer')
        const globalState = useGlobalState();
        return { text, globalState}
    },

});
