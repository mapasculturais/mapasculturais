app.component('entity-cards', {
    template: $TEMPLATES['entity-cards'],

    props: {
        classes: {
            type: [String, Array, Object],
            required: false
        },
        type: {
            type: String,
            default: '',
        },
    },

    setup({ slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('entity-cards')
        return { text, hasSlot }
    },

    data() {
        const cards = $MAPAS.config.homeMetabase.filter((c) => {
            return c.type == this.type
        });

        cards.map((c) => {
            c.data.map((d) => {
                if (d.id == 'agentes-cadastrados-7-dias') {
                    const today = new Date();
                    const sevenDaysBefore = new Date();
                    sevenDaysBefore.setDate(today.getDate() - 7);

                    const newData = d.data.filter(dd => {
                        const dateObject = new Date(dd.createTimestamp.date);
                        return dateObject >= sevenDaysBefore && dateObject <= today;
                    });

                    d.value = newData.length;
                    return d;
                }

                return d;
            });
        });
        
        return {
            cards: cards,
        }
    },

    computed: { },

    methods: {
        lengthClass(text) {
            const textString = String(text);
            if (textString.length > 5) {
                return 'metabase-card__number--long';
            }
        },
    },
});
