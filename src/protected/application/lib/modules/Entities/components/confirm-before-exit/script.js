app.component('confirm-before-exit', {
    template: $TEMPLATES['confirm-before-exit'],

    props: {
        unsaved: {
            type: Boolean,
            required: true
        },
    },
    data() {
        return {
            triggered: false,

        }
    },
    mounted() {
        window.onbeforeunload = (event) => {
                return Boolean(this.unsaved);
        }
    },
    
});