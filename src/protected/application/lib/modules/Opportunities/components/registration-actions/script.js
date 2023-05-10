app.component('registration-actions', {
    template: $TEMPLATES['registration-actions'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },

    setup() {
        const text = Utils.getTexts('registration-actions')
        return { text }
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

    data() {
        return {
            fields: $MAPAS.registrationFields,
        }
    },
    
    methods: {
        fieldName(field) {
            if (field == 'agent_instituicao') {
                return this.text('Instituição responsável'); 
            }

            if (field == 'agent_coletivo') {
                return this.text('Agente coletivo');
            }

            if (field == 'projectName') {
                return this.text('Nome do projeto');
            }

            if (field == 'space') {
                return this.text('Espaço');
            }

            if (field.slice(0, 6) == 'field_') {
                for (let regField of this.fields) {
                    if (regField.fieldName == field) {
                        return regField.title;
                    }
                }
            }

            return this.text('Campo não identificado');

        },
        async send() {
            const data = {id: this.registration.id};
            if (this.registration.category) {
                data.category = this.registration.category;
            }
            
            try {
                this.registration.disableMessages();
                await this.save();
                this.registration.enableMessages();
                await this.registration.POST('send', {data});
                document.location.reload();
            } catch(error) {
                console.error(error);
            }
        },
        async validate() {
            await this.registration.POST('validateEntity', {});
        },
        async save() {
            const iframe = document.getElementById('registration-form');
            const registration = this.registration;
            if (iframe) {
                const promise = new Promise((resolve, reject) => {
                    Promise.all([
                        registration.save(false),
                    ]).then((values) => {
                        resolve(values[0]);
                    });
                });
                return promise;

            } else {
                return registration.save(false);
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
