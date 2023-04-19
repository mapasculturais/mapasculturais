app.component('mc-side-menu', {
    template: $TEMPLATES['mc-side-menu'],
    emits: ['toggle'],

    props: {
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
            isOpen: false,
        }
    },
    
    methods: {
        emitToggle () {
            this.$emit('toggle');
        },
        stopPropagation (event) {
            event.stopPropagation();
        },
        toggleMenu () {
            this.isOpen= this.isOpen ? false : true; 
        },
    },
});
