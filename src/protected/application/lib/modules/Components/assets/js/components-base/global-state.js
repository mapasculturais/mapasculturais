const rawProfile = globalThis.$MAPAS.userProfile;
if(rawProfile) {
    const agentAPI = new API('agent');
    const profile = agentAPI.getEntityInstance(rawProfile.id);
    profile.populate(rawProfile);
    globalThis.$MAPAS.userProfile = profile;
}

const rawUser = globalThis.$MAPAS.user;
if(rawUser) {
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
            auth
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
