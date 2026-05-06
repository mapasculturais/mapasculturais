app.component('personal-access-token--card', {
    template: $TEMPLATES['personal-access-token--card'],
    emits: ['revoked'],
    props: {
        entity: { type: Object, required: true },
    },
    setup() {
        const text = Utils.getTexts('personal-access-token--card');
        return { text };
    },
    data() {
        return {
            revoking: false,
        };
    },
    computed: {
        isExpired() {
            if (!this.entity.expiresAt) return false;
            const d = this.entity.expiresAt._date ? this.entity.expiresAt._date : this.entity.expiresAt;
            return new Date(d) < new Date();
        },
    },
    methods: {
        revoke() {
            if (this.revoking) return;
            this.revoking = true;
            this.entity.delete().then(() => {
                this.revoking = false;
                this.$emit('revoked');
            }).catch(() => {
                this.revoking = false;
            });
        },
    },
});
