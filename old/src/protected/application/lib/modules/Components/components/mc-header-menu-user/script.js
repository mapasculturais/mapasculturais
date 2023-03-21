app.component('mc-header-menu-user', {
    template: $TEMPLATES['mc-header-menu-user'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        return {
            profile: $MAPAS.userProfile,
            open: false
        }
    },

    methods: {
        toggleMobile() {
            this.open = !this.open;
        }
    }
});
