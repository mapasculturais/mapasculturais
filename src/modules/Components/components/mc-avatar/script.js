app.component('mc-avatar', {
    template: $TEMPLATES['mc-avatar'],

    props: {
        entity: {
            type: [Entity , Object],
            required: true,

        },
        size: {
            type: String,
            default: 'medium',
            required: true,
            validator: (value) => ['big', 'medium', 'small', 'xsmall'].includes(value)
        },
        square: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        classes() {
            return [`mc-avatar--${this.size}`, { 'mc-avatar--icon': !this.image }, { 'mc-avatar--square': this.square }]
        },
        image() {
            return this.entity.files.avatar?.transformations?.avatarSmall?.url;
        }
    },
    methods: {
    }
});
