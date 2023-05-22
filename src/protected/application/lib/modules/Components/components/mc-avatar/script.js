app.component('mc-avatar', {
    template: $TEMPLATES['mc-avatar'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },
    computed: {
        image() {
            return this.entity.files.avatar?.transformations?.avatarSmall?.url;
        }
    }
});
