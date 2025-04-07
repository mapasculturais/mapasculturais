app.component('entity-field-links', {
    template: $TEMPLATES['entity-field-links'],
    emits: ['change'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: true
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
        if (typeof existingLinks === 'string') {
            try {
                const parsed = JSON.parse(existingLinks);
                this.links = Array.isArray(parsed) ? parsed : [parsed];
            } catch (error) {
                console.error('String inválida para JSON:', error);
                this.links = [];
            }
        } else if (Array.isArray(existingLinks)) {
            this.links = existingLinks;
        } else if (typeof existingLinks === 'object' && existingLinks !== null) {
            this.links = [existingLinks];
        } else {
            this.links = [];
        }
        this.links = this.links.filter(Boolean);
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
