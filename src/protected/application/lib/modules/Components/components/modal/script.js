app.component('modal', {
    template: $TEMPLATES['modal'],
    emits: ['open', 'close'],

    data() {
        return {
            modalOpen: false,
            loading: {active: false}
        }
    },
    props: {
        title: String,
        closeButton: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        open () {
            this.modalOpen = true;
            this.$emit('open', this);
        },
        close () {
            this.modalOpen = false;
            this.$emit('close', this);
        }
    },
});
