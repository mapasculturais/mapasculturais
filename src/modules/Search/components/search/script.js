app.component('search', {
    template: $TEMPLATES['search'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search');
        const globalState = useGlobalState();
        return { text, globalState }
    },

    props: {
        pageTitle: {
            type: String,
            required: true
        },
        entityType: {
            type: String,
            required: true
        },
        initialPseudoQuery: {
            type: Object,
            required: true
        }
    },

    data() {
        let pseudoQuery;
        if (this.initialPseudoQuery && $MAPAS.initialPseudoQuery) {
            pseudoQuery = { ...$MAPAS.initialPseudoQuery, ...this.initialPseudoQuery };
        } else {
            pseudoQuery = this.initialPseudoQuery || $MAPAS.initialPseudoQuery || {};
        }
        return { pseudoQuery };
    },

    computed: {
    },

    methods: {
        changeTab(tab) {
            if (tab.tab.slug == 'map') {
                this.globalState.hideFooter();
            } else {
                this.globalState.showFooter();
            }
        }
    },
});
