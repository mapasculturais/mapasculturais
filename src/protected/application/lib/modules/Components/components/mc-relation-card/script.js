app.component('mc-relation-card', {
    template: $TEMPLATES['mc-relation-card'],

    props: {
        relation: {
            type: [Entity, Object],
            required: true
        },
    },
});
