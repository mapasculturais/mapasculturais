app.component('panel--nav', {
    template: $TEMPLATES['panel--nav'],

    props: {
        classes: {
            type: [Array, String],
            default: ''
        }
    },

    data() {
        const global = useGlobalState();
        const leftGroups = $MAPAS.config.panelNav.filter((group)=>{
            if(group.column != 'right') {
                return group;
            }
        });
        const rightGroups = $MAPAS.config.panelNav.filter((group)=>{
            if(group.column == 'right') {
                return group;
            }
        });

        return {
            entity: global.auth.user?.profile,
            grouspColumn : $MAPAS.config.panelNav,
            leftGroups,
            rightGroups,


        }
    },
    props: {
        viewport: {
            type: String,
            default: 'desktop'
        }
    },



    methods: {
        active(item) {
            const route = $MAPAS.route.route;
            return $MAPAS.activeNav == item.route || route == item.route;
        }
    }
})
