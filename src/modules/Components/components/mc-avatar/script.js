app.component('mc-avatar', {
    template: $TEMPLATES['mc-avatar'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        size: {
            type: String,
            default: 'medium',
            required: true,
            validator: (value) => ['big', 'medium', 'small', 'xsmall'].includes(value)
        },
    },
    computed: {
        classes() {
            return [`mc-avatar--${this.size}`, { 'mc-avatar--icon': !this.image }]
        },
        image() {
            return this.entity.files.avatar?.transformations?.avatarSmall?.url;
        }
    }
});
