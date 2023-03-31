app.component('opportunity-phase-publish-date-config' , {
    template: $TEMPLATES['opportunity-phase-publish-date-config'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-publish-date-config');
        return { text };
    },

    props: {
        phase: {
            type: Entity,
            required: true
        },
        hideCheckbox: {
            type: Boolean,
            default: false
        },
        hideDescription: {
            type: Boolean,
            default: false
        }
    },

    computed: {
        isBlockPublish () {
            const date = this.phase.evaluationMethodConfiguration?.evaluationTo || this.phase.registrationTo;
            return !!date ? date.isFuture() : false;
        }
    },

    methods: {
        publishRegistration () {
            this.phase.POST('publishRegistration');
        },
        unpublishRegistration () {
            this.phase.POST('unpublishRegistration');
        }
    }
});