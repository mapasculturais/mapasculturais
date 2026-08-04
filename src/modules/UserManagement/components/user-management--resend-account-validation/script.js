app.component('user-management--resend-account-validation', {
    template: $TEMPLATES['user-management--resend-account-validation'],

    setup() {
        const text = Utils.getTexts('user-management--resend-account-validation');
        const messages = useMessages();
        return { text, messages };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    data() {
        const config = $MAPAS.accountValidation || {};

        return {
            // Ausência da chave significa validada, espelhando o padrão do backend:
            // provedor sem validação de conta não tem conta pendente.
            supported: config.supported || false,
            validated: config.validated !== false,
            canResend: config.canResend || false,
            sending: false,
            sent: false,
        };
    },

    computed: {
        showButton() {
            return this.supported && this.canResend && !this.validated;
        }
    },

    methods: {
        parseApiError(error) {
            if (!error) {
                return '';
            }
            if (typeof error.data === 'string') {
                return error.data;
            }
            if (error.message) {
                return error.message;
            }
            return '';
        },

        async resend() {
            if (this.sending) {
                return;
            }

            this.sending = true;

            try {
                const url = Utils.createUrl('panel', 'resendAccountValidationEmail');
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ userId: this.entity.id })
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok && !data.error) {
                    this.sent = true;
                    this.sendMessage(data.message || this.text('successMessage'), 'success');
                } else {
                    this.sendMessage(this.parseApiError(data) || this.text('errorMessage'), 'error');
                }
            } catch (e) {
                this.sendMessage(this.text('errorMessage'), 'error');
            } finally {
                this.sending = false;
            }
        },

        sendMessage(message, type = 'success') {
            this.messages[type](message);
        }
    }
});
