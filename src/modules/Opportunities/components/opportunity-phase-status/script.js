app.component('opportunity-phase-status' , {
    template: $TEMPLATES['opportunity-phase-status'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-status');
        return { text };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        phases: {
            type: Array,
            required: true
        },
        tab: {
            type: String,
        },

        /**
         * R02/#65 — guard de instância: false quando este componente é
         * instanciado para a fase de recurso (opportunity-appeal-phase-config),
         * repassado ao opportunity-phase-publish-config-registration.
         */
        mainPhaseOnly: {
            type: Boolean,
            default: true
        },

        /**
         * Oculta a publicação nesta seção. Usado pela fase de recurso, que não
         * está em `phases` (nextPhase não resolve a avaliação seguinte): a
         * publicação fica só na seção da avaliação, como nas demais fases.
         */
        hidePublish: {
            type: Boolean,
            default: false
        },
    },

    computed: {
        index() {
            return this.phases.indexOf(this.entity);
        },

        previousPhase() {
            return this.phases[this.index - 1];
        },

        nextPhase() {
            return this.phases[this.index + 1];
        },
    }
});