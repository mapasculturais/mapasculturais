app.component('registration-info', {
    template: $TEMPLATES['registration-info'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

});
