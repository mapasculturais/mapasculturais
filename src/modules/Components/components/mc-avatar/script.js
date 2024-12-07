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

        type: {
            type: String,
            default: null,
            validator: (value) => ['warning', 'danger'].includes(value)
        },
    },
    computed: {
        classes() {
            return [`mc-avatar--${this.size}`, { 'mc-avatar--icon': !this.image }, { 'mc-avatar--square': this.square }, { [`mc-avatar--${this.type}`]: this.type }];
        },
        image() {
            if (this.type) {
                return null;
            }

            const map = {
                big: 'avatarBig',
                medium: 'avatarBig',
                small: 'avatarMedium',
                xsmall: 'avatarSmall'
            };

            const size = map[this.size];

            const transformations = this.entity?.files?.avatar?.transformations;
            
            if(transformations) {
                return transformations[size]?.url;
            } else if(this.entity?.avatar) {
                return this.entity?.avatar[size]?.url;
            } else {
                return undefined;
            }
        }
    },
    methods: {
    }
});
