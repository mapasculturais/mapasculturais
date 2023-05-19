app.component('confirm-before-exit', {
    template: $TEMPLATES['confirm-before-exit'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    mounted() {        
        window.onbeforeunload = (event) => {
            if (Object.keys(this.entity.data(true)).length > 0) {
                return true;
            }
        }
    },
});