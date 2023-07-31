const rawUser = globalThis.$MAPAS.user;

if (rawUser) {
    const userAPI = new API('user');
    const user = userAPI.getEntityInstance(rawUser.id);
    user.populate(rawUser);
    globalThis.$MAPAS.user = user;
}

globalThis.useGlobalState = Pinia.defineStore('globalState', {
    state: () => {
        const auth = {
            isLoggedIn: !!globalThis.$MAPAS.userId,
            isGuest: !globalThis.$MAPAS.userId,
            user: globalThis.$MAPAS.userId ? $MAPAS.user : null,
            is(role) {
                return globalThis.$MAPAS.currentUserRoles.includes(role)
            },
        }
        return {
            visibleFooter: true,
            auth,
            showTemplateHook: false,
        }
    },

    actions: {
        showFooter() {
            this.visibleFooter = true
        },

        hideFooter() {
            this.visibleFooter = false
        },
    }
});

globalThis.app.use({
    install(app) {
        app.config.globalProperties.global = useGlobalState();
    }
});

globalThis.addEventListener('keydown', function(event) {
    if(event.ctrlKey && event.shiftKey && event.altKey) {
        const global = useGlobalState();
        global.showTemplateHook = !global.showTemplateHook;
    }
})