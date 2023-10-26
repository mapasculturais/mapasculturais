app.component('error-display', {
    template: $TEMPLATES['error-display'],
    components: {
        VueRecaptcha
    },
    setup() {
        const text = Utils.getTexts('error-display')
        return { text }
    },
    props: {
        error: {
            type: String,
            required: '',
        }
    },

    methods: {
        verifyError() {

        }
    },
});
