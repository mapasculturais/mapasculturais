app.component('entity-profile', {
    template: $TEMPLATES['entity-profile'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    computed: {
        isRequired() {
            const config = $MAPAS.config.EntityProfile?.requiredAvatarByEntityType ?? {};
            return Boolean(config[this.entity.__objectType]);
        },
    },
});
