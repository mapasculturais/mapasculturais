app.component('mc-modal', {
    template: $TEMPLATES['mc-modal'],
    emits: ['open', 'close'],

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
        escToClose: {
            type: Boolean,
            default: true
        },
        clickToClose: {
            type: Boolean,
            default: true
        },
        teleport: {
            type: null,
            default: false
        },
    },
    data() {
        return {
            processing: false,
            modalOpen: false
        }
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
        },
        closed() {
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
