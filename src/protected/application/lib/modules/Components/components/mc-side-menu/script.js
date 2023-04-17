app.component('mc-side-menu', {
    template: $TEMPLATES['mc-side-menu'],
    emits: ['toggle'],

    props: {
        isOpen: {
            type: Boolean,
            required: true
        },
        textButton: {
            type: String,
            default: 'Button'
        },
        content: {
            type: String,
            default: 'Content'
        },
    },

    setup() {
        const text = Utils.getTexts('mc-side-menu');
        return { text }
    },

    mounted() {
    },

    data() {
        return {

        }
    },
    
    methods: {
        emitToggle () {
            this.$emit('toggle');
        },
        stopPropagation (event) {
            event.stopPropagation();
        }
    },
});
