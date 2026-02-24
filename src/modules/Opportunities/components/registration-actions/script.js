app.component('registration-actions', {
    template: $TEMPLATES['registration-actions'],

    emits: ['update:stepIndex'],

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
            fields: Vue.markRaw($MAPAS.registrationFields),
            hideErrors: false,
            isValidated: false,
            validationErrors: this.getEmptyValidationState(),
            descriptions: $DESCRIPTIONS.registration,
            scrolling: false,
        }
    },

    computed: {
        additionalValidateFields() {
            return $MAPAS.config.registrationActions.additionalValidateFields;
        },
        additionalValidateFieldsSteps() {
            return $MAPAS.config.registrationActions.additionalValidateFieldsSteps;
        },
        canSubmit() {
            return this.isLastStep && this.isValidated;
        },

        fieldsMap () {
            const entries = this.fields.map((field) => [field.fieldName, field]);
            return Object.fromEntries(entries);
        },

        hasErrors() {
            for (const stepErrors of Object.values(this.validationErrors)) {
                if (Object.keys(stepErrors).length > 0) {
                    return true;
                }
            }
            return false;
        },

        isLastStep() {
            return this.stepIndex === this.steps.length - 1;
        },

        sortedValidationErrors () {
            const errors = {};
            for (const [stepIndex, step] of Object.entries(this.steps)) {
                if (this.validationErrors[step._id]) {
                    const stepErrors = this.validationErrors[step._id];
                    const fieldEntries = Object.entries(stepErrors);
                    fieldEntries.sort(([a], [b]) => {
                        return Math.sign(this.fieldsMap[a]?.displayOrder - this.fieldsMap[b]?.displayOrder);
                    });
                    errors[stepIndex] = Object.fromEntries(fieldEntries);
                }
            }
            return errors;
        },

        step() {
            return this.steps[this.stepIndex];
        },

        confirmButtonTitle() {
            return this.registration.opportunity.isAppealPhase 
                ? this.text('Quer enviar seu recurso?') 
                : this.text('Quer enviar sua inscrição?');
        },
         formatEditableUntil() {
            return `${this.registration.editableUntil.date('numeric year')} às ${this.registration.editableUntil.time('2-digit')}`
        }
    },

    watch: {
        async stepIndex() {
            if (!this.scrolling) {
                document.querySelector('.section__title')?.scrollIntoView({ behavior: 'instant', block: 'start' });
            }
        },
    },

    methods: {
        canSeeAction() {
            if(this.registration.currentUserPermissions.modify || this.registration.currentUserPermissions.sendEditableFields) {
               return true;
            }

            return false;
        },
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

            if (field == 'workplan') {
                return this.text('Plano de metas');
            }

            if (field == 'projectDuration') {
                return this.text('Duração do projeto (meses)');
            }

            if (field == 'culturalArtisticSegment') {
                return this.text('Segmento artistico-cultural');
            }

            if (field == 'goal') {
                return this.text('Meta');
            }

            if (field == 'delivery') {
                return this.text('Entrega');
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

        async send(modal) {
            let result;
            try {
                this.registration.disableMessages();
                await this.save();
                this.registration.enableMessages();

                result = await this.validate();
            } catch(error) {
                console.error(error);
                result = false;
            }

            if(!result) { 
                modal.close();
                return;
            }
            
            const route = this.editableFields ? 'sendEditableFields' : 'send';
            const data = {id: this.registration.id};
            if (this.registration.category) {
                data.category = this.registration.category;
            }

            try {
                await this.registration.POST(route, {data, processingMessage: this.text('Enviando')});
                if(this.editableFields) {
                    document.location = this.registration.singleUrl;
                } else {
                    document.location.reload();
                }
            } catch(error) {
                console.error(error);
            }

            modal.close();
        },

        async validate() {
            const messages = useMessages();

            // VALIDAÇÃO: Verificar campos custom-table antes de enviar ao backend
            const customTableFields = this.fields.filter(field => field.fieldType === 'custom-table');
            
            for (const field of customTableFields) {
                const tableData = this.registration[field.fieldName];
                const columns = field.config?.columns || [];
                
                if (!Array.isArray(tableData) || tableData.length === 0) {
                    continue; // Tabela vazia, próximo campo
                }
                
                for (let rowIndex = 0; rowIndex < tableData.length; rowIndex++) {
                    const row = tableData[rowIndex];
                    
                    for (let colIndex = 0; colIndex < columns.length; colIndex++) {
                        const column = columns[colIndex];
                        const value = row[`col${colIndex}`];
                        
                        // Verificar campo obrigatório
                        if (column.required === 'true' && (!value || value.trim() === '')) {
                            messages.error(`Campo "${column.name}" (linha ${rowIndex + 1}) em "${field.title}" é obrigatório`);
                            return false;
                        }
                        
                        // Verificar validação específica do tipo
                        if (value && value.trim() !== '') {
                            let isValid = true;
                            let errorMessage = '';
                            
                            switch(column.type) {
                                case 'cpf':
                                    isValid = this.validateCPF(value);
                                    errorMessage = `CPF inválido no campo "${column.name}" (linha ${rowIndex + 1}) em "${field.title}"`;
                                    break;
                                case 'email':
                                    isValid = this.validateEmail(value);
                                    errorMessage = `E-mail inválido no campo "${column.name}" (linha ${rowIndex + 1}) em "${field.title}"`;
                                    break;
                            }
                            
                            if (!isValid) {
                                messages.error(errorMessage);
                                return false;
                            }
                        }
                    }
                }
            }

            try {
                await this.save();
                const success = await this.registration.POST('validateEntity', {processingMessage: this.text('Validando')});

                if (success) {
                    this.isValidated = true;
                    this.validationErrors = this.getEmptyValidationState();
                    messages.success(this.text('Validado'));
                }

                return success;
            } catch (error) {
                if (error?.data) {
                    const validationErrors = this.groupValidationErrors(error.data);
                    Object.assign(this.validationErrors, validationErrors);
                }
                return false;
            }
        },

        validateCPF(cpf) {
            if (!cpf) return true;
            
            cpf = cpf.replace(/[^\d]/g, '');
            
            if (cpf.length !== 11) return false;
            if (/^(\d)\1{10}$/.test(cpf)) return false;
            
            let soma = 0;
            for (let i = 0; i < 9; i++) {
                soma += parseInt(cpf.charAt(i)) * (10 - i);
            }
            let resto = soma % 11;
            let digito1 = resto < 2 ? 0 : 11 - resto;
            
            if (parseInt(cpf.charAt(9)) !== digito1) return false;
            
            soma = 0;
            for (let i = 0; i < 10; i++) {
                soma += parseInt(cpf.charAt(i)) * (11 - i);
            }
            resto = soma % 11;
            let digito2 = resto < 2 ? 0 : 11 - resto;
            
            return parseInt(cpf.charAt(10)) === digito2;
        },

        validateEmail(email) {
            if (!email) return true;
            
            const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return regex.test(String(email).toLowerCase());
        },

        getEmptyValidationState() {
            const validationErrors = {};
            for (const step of this.steps) {
                validationErrors[step._id] = {};
            }
            return validationErrors;
        },

        groupValidationErrors(errors) {
            const validationErrors = this.getEmptyValidationState();

            for (const [fieldName, fieldError] of Object.entries(errors)) {
                if (fieldName.startsWith('field_')) {
                    for (field of this.fields) {
                        if (field.fieldName === fieldName) {
                            validationErrors[field.step.id][fieldName] = fieldError;
                        }
                    }
                }
                if (fieldName.startsWith('file_')) {
                    const groupName = fieldName.replace('file_', 'rfc_');
                    for (const field of this.fields) {
                        if (field.groupName === groupName) {
                            validationErrors[field.step.id][fieldName] = fieldError;
                        }
                    }
                }

                if (this.additionalValidateFields.includes(fieldName)) {
                    const keys = Object.keys(validationErrors);
                    const lastStep = keys[keys.length - 1]; 

                   if (this.fields.length > 0) {
                        const step = this.additionalValidateFieldsSteps?.[fieldName] || lastStep;
                        validationErrors[step][fieldName] = fieldError;
                   } else {
                        validationErrors[Object.keys(validationErrors)[0]][fieldName] = fieldError;
                   }
                }
            }

            return validationErrors;
        },
        async save() {
            try{
                await this.registration.save(0, false, true);
                this.isValidated = false;
                this.validationErrors = this.getEmptyValidationState();
                return true;
            } catch (error) {

                if (error?.data) {
                    this.isValidated = true;

                    const validationErrors = this.groupValidationErrors(error.data);
                    Object.assign(this.validationErrors, validationErrors);
                }
                return false;
            }
        },

        async saveAndExit(modal) {
            if(await this.save()) {
                modal.open();
            }
        },

        exit() {
            window.location.href = this.registration.opportunity.singleUrl;
        },

        goToField(stepIndex, fieldName) {
            this.goToStep(Number(stepIndex));
            this.$nextTick(() => {
                this.scrolling = true;
                window.setTimeout(() => this.scrolling = false, 100);
                document.querySelector(`[data-field="${fieldName}"]`)?.scrollIntoView({ behavior: 'instant', block: 'center' });
            });
        },

        goToStep(stepIndex) {
            if (stepIndex >= 0) {
                this.$emit('update:stepIndex', stepIndex);
            }
        },

        stepName(stepIndex) {
            return `${Number(stepIndex) + 1}. ${this.steps[stepIndex].name}`;
        },

        async previousStep() {
            this.$emit('update:stepIndex', this.stepIndex - 1);
        },

        async nextStep() {
            this.$emit('update:stepIndex', this.stepIndex + 1);
        },
    },
});
