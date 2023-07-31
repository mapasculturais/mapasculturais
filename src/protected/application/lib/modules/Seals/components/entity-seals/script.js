app.component('entity-seals', {
    template: $TEMPLATES['entity-seals'],

    computed: {
        query() {
            const ids = this.entity.seals.map((item) => item.sealId).join(',');
            return ids ? { id: `!IN(${ids})` } : {};
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: __('verificacoes', 'entity-seals'),
        },
        editable: {
            type: Boolean,
            default: false
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        showName: {
            type: Boolean,
            default: false
        },
    },

    methods: {
        addSeal(seal) {
            this.entity.createSealRelation(seal);
        },
        removeSeal(seal) {
            this.entity.removeSealRelation(seal);
        }
    }
});
