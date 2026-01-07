app.component('mc-collapsible', {
    template: $TEMPLATES['mc-collapsible'],

    setup(props, { slots }) {
        return { hasSlot: name => !!slots[name] }
    },

    props: {
        open: { type: Boolean, default: false },
    },

    data() {
        return { isOpen: this.open }
    },

    watch: {
        open(val) { this.isOpen = val }
    },

    methods: {
        toggle() { this.isOpen = !this.isOpen },
        expand() { this.isOpen = true },
        close() { this.isOpen = false }
    }
});
