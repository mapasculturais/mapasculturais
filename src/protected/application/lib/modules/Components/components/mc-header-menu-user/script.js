app.component('mc-header-menu-user', {
    template: $TEMPLATES['mc-header-menu-user'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        return {
            open: false
        }
    },

    methods: {
        toggleMobile() {            
            document.body.classList.toggle("no-scroll");
            this.open = !this.open;
        }
    }
});
