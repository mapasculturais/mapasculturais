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
        hideButton: {
            type: Boolean,
            default: false
        },
        buttonPosition: {
            type: String,
            default: 'left'
        },
        hideDatepicker: {
            type: Boolean,
            default: false
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

    methods: {
        publishRegistration () {
            this.phase.POST('publishRegistration');
        },
        unpublishRegistration () {
            this.phase.POST('unpublishRegistration');
        }
    }
});