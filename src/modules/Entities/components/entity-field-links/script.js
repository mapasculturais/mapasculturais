app.component('entity-field-links', {
    template: $TEMPLATES['entity-field-links'],
    emits: ['change'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        prop: {
            type: String,
            required: true
        },

        showTitle: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            links: []
        }
    },

    created() {
        const existingLinks = this.entity[this.prop] || [];
        this.links = Array.isArray(existingLinks) ? existingLinks : [];
    },

    methods: {
        addLink() {
            this.links.push({ title: '', value: '' });
            this.$emit('change', this.links);
        },

        removeLink(index) {
            this.links.splice(index, 1);
            this.$emit('change', this.links);
        },
    },
});
