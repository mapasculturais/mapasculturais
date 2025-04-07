app.component('panel--nav', {
    template: $TEMPLATES['panel--nav'],

    props: {
        classes: {
            type: [Array, String],
            default: ''
        },

        sidebar: {
            type: Boolean,
            default: false
        },

        viewport: {
            type: String,
            default: 'desktop'
        }
    },

    data() {
        const global = useGlobalState();
        const sidebar = this.sidebar;
        const leftGroups = $MAPAS.config.panelNav.filter((group)=>{
            if(group.column == 'user' && sidebar) {
                return;
            }
            if(group.column == 'left' || sidebar) {
                return group;
            }
        });
        const rightGroups = $MAPAS.config.panelNav.filter((group)=>{
            if(group.column == 'right' && !sidebar) {
                return group;
            }
        });
        const userGroup = $MAPAS.config.panelNav.filter((group)=>{
            if(group.column == 'user') {
                return group;
            }
        })[0];

        return {
            entity: global.auth.user?.profile,
            grouspColumn : $MAPAS.config.panelNav,
            leftGroups,
            rightGroups,
            userGroup,
        }
    },

    methods: {
        active(item) {
            const route = $MAPAS.route.route;
            return $MAPAS.activeNav == item.route || route == item.route;
        }
    }
})
