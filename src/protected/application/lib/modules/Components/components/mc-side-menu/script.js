app.component('mc-side-menu', {
    template: $TEMPLATES['mc-side-menu'],
    emits: ['toggle'],

    props: {
        isOpen: {
            type: Boolean,
            required: true
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
    },
});
