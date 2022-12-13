app.component('user-accepted-terms', {
    template: $TEMPLATES['user-accepted-terms'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        // Termo
        const terms = $MAPAS.config.LGPD;

        return {
            terms
        };
    },

    props: {
        user: {
            type: Entity,
            required: true
        },

    },
    computed: {
        // politica de privacidade
        privacyPolicy() {

            var accepted = {};
            for (const [acceptedmd5, term] of Object.entries(this.user.lgpd_privacyPolicy)) {

                accepted.ip = term.ip;
                accepted.timestamp = term.timestamp;
                accepted.md5 = term.md5;
                accepted.acceptedmd5 = acceptedmd5;
                accepted.slug = __('Política de privacidade', 'user-accepted-terms');
            }
            return accepted;
        },
        termsOfUsage() {
            var accepted = {};
            for (const [acceptedmd5, term] of Object.entries(this.user.lgpd_termsOfUsage)) {

                accepted.ip = term.ip;
                accepted.timestamp = term.timestamp;
                accepted.md5 = term.md5;
                accepted.acceptedmd5 = acceptedmd5;
                accepted.slug = __('Termos de uso', 'user-accepted-terms');

            }
            return accepted;
        },
        termsUse() {
            var accepted = {};
            for (const [acceptedmd5, term] of Object.entries(this.user.lgpd_termsUse)) {

                accepted.ip = term.ip;
                accepted.timestamp = term.timestamp;
                accepted.md5 = term.md5;
                accepted.acceptedmd5 = acceptedmd5;
                accepted.slug = __('Autorização de uso de imagem', 'user-accepted-terms');

            }
            return accepted;
        }
    },

    methods: {

    },
});
