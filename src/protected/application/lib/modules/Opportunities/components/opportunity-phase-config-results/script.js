app.component('opportunity-phase-config-results' , {
    template: $TEMPLATES['opportunity-phase-config-results'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-results');
        return { text };
    },

    data () {
        return {};
    },

    props: {
        currentIndex: {
            type: Number,
            required: true
        },
        entity: {
            type: Entity,
            required: true
        },
        phases: {
            type: Array,
            required: true
        }
    },

    computed: {
    },

    mounted () {
    },

    methods: {
        addPublishRegistrations (phase) {
            phase.POST('publishRegistrations');
        },
        isBlockedPublish (index) {
            const previousPhase = this.getPreviousPhase(index);
            const dtFinal = previousPhase.evaluationTo?._date || null;
            return dtFinal > new Date();
        }
    }
});