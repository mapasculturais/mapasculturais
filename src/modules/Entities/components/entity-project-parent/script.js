app.component('entity-project-parent', {
    template: $TEMPLATES['entity-project-parent'],
    emits: [],

    setup() {
        const text = Utils.getTexts('entity-project-parent');
        return { text };
    },

    data() {
        return {
            query: {
                id: `!EQ(${this.entity.id})`
            }
        };
    },

    computed: {
        parent() {
            return this.entity.parent || null;
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        emptyMessage: {
            type: String,
            default: __('empty', 'entity-project-parent')
        }
    },

    methods: {
        changeParent(entity) {
            this.entity.parent = entity;
            this.entity.save();
        },

        removeParent() {
            this.entity.parent = null;
            this.entity.save();
        }
    }
});
