app.component('mc-avatar', {
    template: $TEMPLATES['mc-avatar'],

    props: {
        class: {
            type: [String, Array, Object]
        },
        entity: {
            type: Entity,
            required: true
        },
    },
    computed: {
        classes() {
            return [this.class, { '-image': !!this.image, '-icon': !this.image }]
        },
        image() {
            return this.entity.files.avatar?.transformations?.avatarSmall?.url;
        }
    }
});
