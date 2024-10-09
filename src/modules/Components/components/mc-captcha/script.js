app.component('mc-captcha', {
    template: $TEMPLATES['mc-captcha'],

    components: {
        VueRecaptcha
    },

    props: {
        config: {
            type: String,
            required: true
        }
    },

    setup() {
        const text = Utils.getTexts('mc-captcha');

        return { text }
    },

    data() {
        const config = $MAPAS.mcCaptchaConfig;

        return {
            provider: config?.captcha?.provider,
            key: config?.captcha.key,
            recaptchaResponse: ''
        }
    },

    mounted() {
        //
    },

    computed: {
        // 
    },

    methods: {
        async verifyCaptcha(response) {
            this.$emit('captcha-verified', response);
        },

        expiredCaptcha() {
            this.$emit('captcha-expired');
        },
    }
});