app.component('mc-captcha', {
    template: $TEMPLATES['mc-captcha'],

    components: {
        VueRecaptcha
    },

    props: {
        config: {
            type: String,
            required: false
        },
        error: {
            type: Boolean,
            required: false
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
            recaptchaResponse: '',
            containerId: `container-captcha-${Math.random().toString(36).slice(2)}`,
            widgetId: null
        }
    },

    mounted() {
        if (this.provider === 'cloudflare') {
            if (window.turnstile && typeof window.turnstile.ready === 'function') {
                window.turnstile.ready(() => this.onloadTurnstileCallback());
            }
        };
    },
    computed: {
        // 
    },

    watch: {
        error(newValue, oldValue) {
            if (newValue) {
                this.expiredCaptcha();
            }
        }
    },

    methods: {
        async verifyCaptcha(response) {
            this.$emit('captcha-verified', response);
        },

        expiredCaptcha() {
            // check if grecaptcha is not defined
            if (typeof grecaptcha !== 'undefined') {
                try {
                    if (this.widgetId !== null) {
                        grecaptcha.reset(this.widgetId);
                    } else {
                        grecaptcha.reset();
                    }
                } catch (e) {}
            }

            // check if turnstile is not defined
            if (typeof window.turnstile !== 'undefined') {
                try {
                    window.turnstile.reset(this.widgetId || undefined);
                } catch (e) {}
            }

            this.$emit('captcha-expired');
        },

        onloadTurnstileCallback() {
            const self = this;
            self.widgetId = turnstile.render(`#${self.containerId}`, {
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