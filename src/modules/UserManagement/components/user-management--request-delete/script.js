app.component('user-management--request-delete', {
    template: $TEMPLATES['user-management--request-delete'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('user-management--request-delete');
        const messages = useMessages();
        return { hasSlot, text, messages };
    },

    data() {
        return {
            requestMessage: '',
            sendCopy: false,
            copyEmail: '',
            processing: false,
        };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    computed: {
        defaultMessage() {
            const profileName = this.entity?.profile?.name || this.entity?.email || '';
            return this.text('defaultMessageText').replace('{{name}}', profileName);
        }
    },

    watch: {
        sendCopy(enabled) {
            if (enabled && !this.copyEmail.trim()) {
                this.copyEmail = this.entity?.email || '';
            }
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

        openRequestModal() {
            this.requestMessage = this.defaultMessage;
            this.sendCopy = false;
            this.copyEmail = '';
            this.$refs.requestModal.open();
        },

        async submit(modal) {
            if (!this.requestMessage.trim()) {
                return;
            }

            this.processing = true;

            try {
                const payload = {
                    message: this.requestMessage.trim(),
                    sendCopy: this.sendCopy,
                    copyEmail: this.sendCopy ? (this.copyEmail.trim() || this.entity.email) : ''
                };

                const api = new API('user');
                const res = await api.POST('requestAccountDeletion', payload);

                const data = await res.json().catch(() => ({}));

                if (res.ok && !data.error) {
                    this.sendMessage(data.message || this.text('successMessage'), 'success');
                    modal.close();
                } else {
                    this.sendMessage(this.parseApiError(data) || this.text('errorMessage'), 'error');
                }
            } catch (e) {
                this.sendMessage(this.text('errorMessage'), 'error');
            } finally {
                this.processing = false;
            }
        },

        sendMessage(message, type = 'success') {
            this.messages[type](message);
        }
    }
});
