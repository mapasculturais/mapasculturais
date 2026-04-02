app.component('oc-reset-default-values', {
    template: $TEMPLATES['oc-reset-default-values'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        prop: {
            type: String,
            required: true
        },
    },
    data() {
        return {}
    },
    methods: {
        async reset() {
            const metaKey = $MAPAS.fromToFilesMetadata[this.prop];
            window.dispatchEvent(new CustomEvent('resetPreviewImage', { detail: { ref: this.prop } }));
            this.entity[metaKey] = null;
            await this.entity.save(300, false);
        }
    }
});
