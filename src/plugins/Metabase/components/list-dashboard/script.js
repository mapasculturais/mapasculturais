const iFrameResizeModule = import('https://unpkg.com/iframe-resizer@4.3.1/js/iframeResizer.min.js');
app.component('list-dashboard', {
    template: $TEMPLATES['list-dashboard'],

    setup({ slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('list-dashboard')
        return { text, hasSlot }
    },
    props: {
        panelId: {
            type: String,
            default: '',
        },
    },
    mounted() {
        // this.$refs.dashboardIframe.src = this.links[this.panelId].link;
        iFrameResizeModule.then(() => {
            iFrameResize({ log: false }, this.$refs.dashboardIframe);
        });
    },

    data() {
        return {};
    },

    computed: {
        link() {
            return this.links[this.panelId].link;
        },
        links() {
            return $MAPAS.config.listDashboard.links;
        },
        names() {
            const result = [];
            Object.keys(this.links).forEach(name => {
                result.push(name);
            })
            return result;
        },
    },

    // watch: {
    //     panelId: {
    //         handler() {
    //             this.$nextTick(()=>{
    //                 this.$refs.dashboardIframe.src = this.links[this.panelId].link;
    //                 iFrameResizeModule.then(() => {
    //                     iFrameResize({ log: false }, this.$refs.dashboardIframe);
    //                 });
    //             });
    //         },
    //         immediate: true,
    //     }
    // },

    methods: {
        getUrl(name) {
            let url = Utils.createUrl('metabase', 'dashboard', { panelId: name });
            return url;
        }
    },
});
