app.component('mc-side-menu', {
    template: $TEMPLATES['mc-side-menu'],
    emits: ['toggle'],

    props: {
        entity: {
            type: Entity,
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
        this.getEvaluations();
        window.addEventListener('previousEvaluation', this.previousEvaluation);
        window.addEventListener('nextEvaluation', this.nextEvaluation);
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
