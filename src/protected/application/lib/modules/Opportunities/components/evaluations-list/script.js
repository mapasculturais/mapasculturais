app.component('evaluations-list', {
    template: $TEMPLATES['evaluations-list'],

    setup() { },

    props: {
        entity: {
            type: Entity,
            required: true
        },

    },

    data() {
        return {
            canEvaluate: this.entity.currentUserPermissions.evaluateRegistrations
        }
    },

    computed: {},

    methods: {},
});
