app.component('registration-actions', {
    template: $TEMPLATES['registration-actions'],

    emits: ['nextStep', 'previousStep'],

    props: {
        registration: {
            type: Entity,
            required: true
        },

        steps: {
            type: Array,
            required: true
        },

        stepIndex: {
            type: Number,
            default: 0
        },

        editableFields: {
            type: Boolean,
            default: false
        }
    },

    setup() {
        const text = Utils.getTexts('registration-actions')
        return { text }
    },

    mounted() {
        const self = this;

        globalThis.addEventListener("message", (event) => {
            if (event.data.type == 'registration.update') {
                let autoSave = false;

                for (let key in event.data.data) {
                    this.registration[key] = event.data.data[key];

                    if(!autoSave) {
                        autoSave = true;
                        
                        clearTimeout(self.autoSaveTimeout);
    
                        self.autoSaveTimeout = setTimeout(() => {
                            self.save();
                        }, $MAPAS.config.registrationActions.autosaveDebounce);
                    }
                }
            }
        });
    },

    data() {
        return {
            fields: $MAPAS.registrationFields,
            hideErrors: false,
            isValidated: false,
            descriptions: $DESCRIPTIONS.registration
        }
    },

    computed: {
        canSubmit () {
            return this.canValidate && this.isValidated;
        },

        canValidate () {
            const isLastStep = this.stepIndex === this.steps.length - 1;
            return isLastStep;
        },

        step () {
            return this.steps[this.stepIndex];
        },
    },

    watch: {
        stepIndex () {
            this.isValidated = false;
        },
    },
    
    methods: {
        toggleErrors() {
            this.hideErrors = !this.hideErrors;
        },

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

            if (field.slice(0, 5) == 'file_') {
                const id = field.slice(5);

                for (let regField of this.fields) {
                    if (regField.groupName == 'rfc_'+id) {
                        return regField.title;
                    }
                }
            }
            
            if(this.descriptions[field]) {
                return this.descriptions[field].label
            }

            return this.text('Campo não identificado');

        },

        async send() {
            const route = this.editableFields ? 'sendEditableFields' : 'send';
            const data = {id: this.registration.id};
            if (this.registration.category) {
                data.category = this.registration.category;
            }
            
            try {
                this.registration.disableMessages();
                await this.save();
                this.registration.enableMessages();
                await this.registration.POST(route, {data});
                if(this.editableFields) {
                    document.location = this.registration.singleUrl;
                } else {
                    document.location.reload();
                }
            } catch(error) {
                console.error(error);
            }
        },

        async validate(step = undefined) {
            const messages = useMessages();
            try {
                if (Object.keys(this.registration.__validationErrors).length > 0) {
                    messages.error(this.text('Corrija os erros indicados'));
                    return false;
                } else {
                    await this.save();
                    const success = await this.registration.POST('validateEntity', { data: { step } });

                    if (success) {
                        this.isValidated = true;

                        if (!step) {
                            messages.success(this.text('Validado'));
                        }
                    }

                    return success;
                }
            } catch (error) {
                console.error(error);
                return false;
            }
        },

        async save() {
            return this.registration.save(300, false);
        },

        exit() {
            this.registration.save().then(() => {
                if (window.history.length > 2) {
                    window.history.back();
                } else {
                    window.location.href = Utils.createUrl('panel', 'index');
                }
            });
        },

        async previousStep() {
            if (await this.validate(this.step._id)) {
                this.$emit('previousStep', this.stepIndex - 1);
            }
        },

        async nextStep() {
            if (await this.validate(this.step._id)) {
                this.$emit('nextStep', this.stepIndex + 1);
            }
        },
    },
});
