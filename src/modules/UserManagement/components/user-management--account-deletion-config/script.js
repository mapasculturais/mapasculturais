app.component('user-management--account-deletion-config', {
    template: $TEMPLATES['user-management--account-deletion-config'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('user-management--account-deletion-config');
        const messages = useMessages();
        return { hasSlot, text, messages };
    },

    data() {
        const cfg = $MAPAS.accountDeletion || {};
        return {
            canConfigure: cfg.canConfigure || false,
            hasSubsite: cfg.hasSubsite || false,
            email: cfg.recipientEmail || '',
            saving: false,
        };
    },

    computed: {
        scopeLabel() {
            return this.hasSubsite ? this.text('subsiteScope') : this.text('globalScope');
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

        async save() {
            const trimmed = this.email.trim();
            if (trimmed && !this.isValidEmail(trimmed)) {
                this.sendMessage(this.text('invalidEmail'), 'error');
                return;
            }

            this.saving = true;

            try {
                const url = Utils.createUrl('panel', 'setAccountDeletionEmail');
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: trimmed })
                });

                if (res.ok) {
                    const data = await res.json();
                    if ($MAPAS.accountDeletion) {
                        $MAPAS.accountDeletion.recipientEmail = data.email;
                    }
                    this.sendMessage(this.text('saveSuccess'), 'success');
                } else {
                    const error = await res.json().catch(() => ({}));
                    this.sendMessage(this.parseApiError(error) || this.text('saveError'), 'error');
                }
            } catch (e) {
                this.sendMessage(this.text('saveError'), 'error');
            } finally {
                this.saving = false;
            }
        },

        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        sendMessage(message, type = 'success') {
            this.messages[type](message);
        }
    }
});
