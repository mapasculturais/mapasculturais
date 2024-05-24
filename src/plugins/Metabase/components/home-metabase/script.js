app.component('home-metabase', {
    template: $TEMPLATES['home-metabase'],

    props: {
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    setup({ slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('home-metabase')
        return { text, hasSlot }
    },

    data() {
        return {
            cards: $MAPAS.config.homeMetabase,
        }
        console.log(links);
    },

    computed: {
        panelURl() {
            return Utils.createUrl('metabase', 'panel');
        },
        names() {
            const result = [];
            Object.keys(this.links).forEach(name => {
                result.push(name);
            })
            return result;
        },
    },

    methods: {
        getUrl(card) {
            return Utils.createUrl('metabase','dashboard', {'panelId':card.panelLink});
        },

        lengthClass(text) {
            const textString = String(text);
            if (textString.length > 5) {
                return 'metabase-card__number--long';
            }
        },
    },
});
