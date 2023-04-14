app.component('registration-evaluation-list', {
    template: $TEMPLATES['registration-evaluation-list'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    setup() {
        const text = Utils.getTexts('registration-evaluation-list');
        return { text }
    },

    mounted() {
    },

    data() {
        return {
            open: false
        }
    },
    
    methods: {
        toggle () {
            this.open = !this.open;
        }
    },
});
