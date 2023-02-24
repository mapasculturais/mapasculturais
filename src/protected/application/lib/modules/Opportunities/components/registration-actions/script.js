app.component('registration-actions', {
    template: $TEMPLATES['registration-actions'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },
    
    methods: {
        save() {
            this.entity.save().then(() => {
                this.exit();
            });
        },
        exit() {
            if (window.history.length > 2) {
                window.history.back();
            } else {
                window.location.href = $MAPAS.baseURL+'panel';
            }
        },
    },
});
