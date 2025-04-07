app.component('support-actions', {
    template: $TEMPLATES['support-actions'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },

    mounted() {
        window.addEventListener("message", (event) => {
            if (event.data.type == 'registration.update') {
                for (let key in event.data.data) {
                    this.registration[key] = event.data.data[key];
                }
            }
        });
    },
    
    methods: {
        async save() {
            const iframe = document.getElementById('support-form');
            if (iframe) {
                const promise = new Promise((resolve, reject) => {
                    this.registration.save(300, false).then(values => resolve(values[0]));
                });
                return promise;
            } else {
                return this.registration.save(300, false);
            }
        },

        exit() {
            this.registration.save().then(() => {
                if (window.history.length > 2) {
                    window.history.back();
                } else {
                    window.location.href = $MAPAS.baseURL+'panel';
                }
            });
        },
    },
});
