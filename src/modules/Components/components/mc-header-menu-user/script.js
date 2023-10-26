app.component('mc-header-menu-user', {
    template: $TEMPLATES['mc-header-menu-user'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        const global = useGlobalState();
        return {
            profile: global.auth.user?.profile,
            open: false
        }
    },

    methods: {
        toggleMobile() {
            this.open = !this.open;
        }
    }
});
