app.component('mc-input-masked-wrapper', {
    template: $TEMPLATES['mc-input-masked-wrapper'],
    emits: ['input'],

    props: {
        value: {
            type: String,
            required: true
        },

        label: {
            type: String,
            required: false
        },

        class: {
            type: [String, Array, Object],
            default: ''
        }
    },

    data () {
        return {}
    },

    computed: {
        model: {
            get () {
                return this.value;
            },
            set (value) {
                this.$emit('input', value);
            }
        }
    }
});
