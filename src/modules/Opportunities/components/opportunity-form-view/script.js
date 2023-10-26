app.component('opportunity-form-view', {
    template: $TEMPLATES['opportunity-form-view'],

    props: {
        classes: {
            type: [String, Array, Object],
            required: false
        },
        entity: {
            type: Entity,
            required: true
        }
    },

    data() {
        let url = Utils.createUrl('visualizacao-de-formulario',[this.entity.id]);
        return { url }
    },
});
