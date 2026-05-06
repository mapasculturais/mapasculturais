app.component('personal-access-token--modal', {
    template: $TEMPLATES['personal-access-token--modal'],
    emits: ['create'],

    setup() {
        const text = Utils.getTexts('personal-access-token--modal');
        return { text };
    },

    data() {
        return {
            entity: null,
            plainTextToken: '',
            copied: false,
            permissionsList: $MAPAS.EntityPermissionsList || {},
        };
    },

    computed: {
        modalTitle() {
            if (this.entity?.id) {
                return __('tokenCriado', 'personal-access-token--modal');
            }
            return __('criarToken', 'personal-access-token--modal');
        },
    },

    methods: {
        createEntity() {
            this.entity = Vue.ref(new Entity('personal-access-token'));
            this.entity.permissions = [];
            this.entity.name = '';
            this.entity.expiresAt = '';
            this.plainTextToken = '';
            this.copied = false;
        },

        destroyEntity() {
            setTimeout(() => {
                this.entity = null;
                this.plainTextToken = '';
            }, 200);
        },

        save(modal) {
            if (!this.entity.name || this.entity.name.length < 3) {
                return;
            }
            if (!this.entity.permissions || this.entity.permissions.length === 0) {
                return;
            }

            modal.loading(true);

            const payload = {
                name: this.entity.name,
                permissions: this.entity.permissions,
            };
            if (this.entity.expiresAt) {
                payload.expiresAt = this.entity.expiresAt;
            }

            const url = Utils.createUrl('personal-access-token', 'index');
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to create token');
                }
                return response.json();
            })
            .then(data => {
                this.entity.id = data.id;
                this.entity.createTimestamp = data.createTimestamp;
                this.entity.tokenPrefix = data.tokenPrefix;
                this.entity.status = data.status;
                this.plainTextToken = data.plainTextToken;
                this.$emit('create', data);
                modal.loading(false);
            })
            .catch(() => {
                modal.loading(false);
            });
        },

        copyToken() {
            navigator.clipboard.writeText(this.plainTextToken).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            });
        },
    },
});
