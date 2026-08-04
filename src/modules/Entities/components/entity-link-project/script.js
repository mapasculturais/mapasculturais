app.component('entity-link-project', {
    template: $TEMPLATES['entity-link-project'],
    emits: [],

    setup() {
        const text = Utils.getTexts('entity-link-project');
        return { text };
    },

    computed: {
        project() {
            return this.entity.project || null;
        },
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: __('vinculado', 'entity-link-project'),
        },
        type: {
            type: String,
            required: true
        },
        editable: {
            type: Boolean,
            default: false
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        label: {
            type: String,
            default: __('link', 'entity-link-project'),
        },
        emptyMessage: {
            type: String,
            default: __('empty', 'entity-link-project'),
        }
    },

    methods: {
        changeProject(entity) {
            this.entity.project = entity;
            this.entity.save();
        },

        removeProject() {
            this.entity.project = null;
            this.entity.save();
        },
    }
});
