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
        if (this.provider === 'cloudflare') {
            window.turnstile.ready(() => this.onloadTurnstileCallback());

            window.verifyCaptcha = this.verifyCaptcha;
            window.expiredCaptcha = this.expiredCaptcha;
        };
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

        onloadTurnstileCallback() {
            const self = this;
            turnstile.render("#container-cloudflare-turnstile", {
                sitekey: self.key,
                callback: function (token) {
                    self.verifyCaptcha(token);
                },
                "expired-callback": function () {
                    self.expiredCaptcha();
                }
            });
        }
    }
});