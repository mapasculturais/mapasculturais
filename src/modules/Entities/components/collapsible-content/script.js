app.component('collapsible-content', {
    template: $TEMPLATES['collapsible-content'],

    setup(props, { slots }) {
        return { hasSlot: name => !!slots[name] }
    },

    props: {
        open: { type: Boolean, default: false },
        classes: { type: [String, Array, Object], required: false }
    },

    data() {
        return { isOpen: this.open }
    },

    watch: {
        open(val) { this.isOpen = val }
    },

    computed: {
        iconName() { return this.isOpen ? 'arrowPoint-up' : 'arrowPoint-down' }
    },
    
    methods: {
        toggle() { this.isOpen = !this.isOpen },
        expand() { this.isOpen = true },
        close() { this.isOpen = false }
    }
});
