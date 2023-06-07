app.component('mc-modal', {
    template: $TEMPLATES['mc-modal'],
    emits: ['open', 'close'],

    data() {
        return {
            modalOpen: false,
            processing: false,
        }
    },
    props: {
        title: {
            type: String,
            default: ''
        },
        classes: {
            type: [String, Array],
            default: '',
        },
        buttonLabel: {
            type: String,
            default: ''
        },
        buttonClasses: {
            type: String,
            default: ''
        },
        closeButton: {
            type: Boolean,
            default: true
        },
        teleport: {
            type: null,
            default: false
        },
    },
    methods: {
        open () {
            this.processing = false;
            this.modalOpen = true;
            this.$emit('open', this);
        },
        close () {
            this.processing = false;
            this.modalOpen = false;
            this.$emit('close', this);
        },
        loading (active) {
            this.processing = active ? true : false 
        },
        toggle() {
            if (this.modalOpen) {
              this.close();
            } else {
              this.open();
            }
          },
    },
});
