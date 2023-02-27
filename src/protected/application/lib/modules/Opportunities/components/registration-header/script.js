app.component('registration-header', {
    template: $TEMPLATES['registration-header'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },
});
