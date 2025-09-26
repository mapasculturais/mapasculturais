app.component('registration-form', {
    template: $TEMPLATES['registration-form'],

    props: {
        registration: {
            type: Entity,
            required: true
        },

        step: {
            type: Entity,
            required: true
        },
    },
    
    setup (props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-form')
        return { text, hasSlot }
    },

    data() {
        const editableFields = this.registration.editableFields ?? [];
        const registrationSnapshot = Object.assign({}, this.registration)

        return {
            editableFields,
            registrationSnapshot,
        }
    },

    computed: {
        disableFields() {
            return $MAPAS.config.registrationForm.disableFields || null;
        },
        description() {
            return $DESCRIPTIONS.registration
        },
        preview () {
            return this.registration.id === -1;
        },
        disabledField() {
            return $MAPAS.requestedEntity.disabledField
        },
        fields () {
            const registration = this.registration;

            const fields = [...$MAPAS.config.registrationForm.fields, ...$MAPAS.config.registrationForm.files];

            fields.sort((a,b) => a.displayOrder - b.displayOrder);

            return fields.filter((field) => {
                if (field.categories?.length && !field.categories.includes(registration.category)) {
                    return false;
                }

                if (field.registrationRanges?.length && !field.registrationRanges.includes(registration.range)) {
                    return false;
                }

                if (field.proponentTypes?.length && !field.proponentTypes.includes(registration.proponentType)) {
                    return false;
                }

                if (field.conditional) {
                    const fieldName = field.conditionalField;
                    const fieldValue = field.conditionalValue;

                    if (fieldName) {
                        if(registration[fieldName] instanceof Array) {
                            if (!registration[fieldName].includes(fieldValue)) {
                                return false;
                            }
                        } else if (registration[fieldName] != fieldValue) {
                            return false;
                        }
                    }
                }

                return true;
            });
        },

        hasCategory () {
            return Boolean(this.registration.opportunity.registrationCategories?.length)
        },

        hasProponentType () {
            return Boolean(this.registration.opportunity.registrationProponentTypes?.length)
        },

        hasRange () {
            return Boolean(this.registration.opportunity.registrationRanges?.length)
        },

        isValid () {
            if (this.hasCategory && !this.registration.category) {
                return false;
            }

            if (this.hasProponentType && !this.registration.proponentType) {
                return false;
            }

            if (this.hasRange > 0 && !this.registration.range) {
                return false;
            }

            return true;
        },

        sections () {
            const sectionSkel = {
                id: '',
                title: '',
                description: '',
            }
            const sections = [];

            let currentSection = { ...sectionSkel, fields: [] };
            for (let field of this.stepFields) {
                if (field.fieldType == 'section') {
                    currentSection = { ...sectionSkel, fields: [] };
                    sections.push(currentSection);

                    currentSection.id = field.fieldName;
                    currentSection.title = field.title;
                    currentSection.description = field.description;
                    continue;
                }

                // se o primeiro campo do formulário não é uma seção será "vazia"
                if (sections.length === 0) {
                    sections.push(currentSection);
                }

                currentSection.fields.push(field);
            }

            return sections;
        },

        stepFields () {
            return this.fields.filter((field) => {
                return field.step?.id === this.step._id;
            });
        },
    },

    methods: {
        showField(field, type) {
            if(field.fieldType == type) {
                return true;
            }

            if(field.fieldType == 'agent-collective-field' || field.fieldType == 'agent-owner-field') {
                if(this.description[field.fieldName].type == type) {
                    return true;
                }
            }
        },
        isDisabled(field) {
            let fieldName = field.fieldName || field.groupName;

            if (this.editableFields.length > 0) {
                if (this.disableFields && this.disableFields.includes(fieldName)) {
                    return true;
                }
            }

            return this.editableFields.length > 0 ? !this.editableFields.includes(fieldName) : false;
        },

        clearFields() {
            this.$nextTick(() => {
                const registration = this.registration;
                const fields = [...$MAPAS.config.registrationForm.fields, ...$MAPAS.config.registrationForm.files];
                
                for(let i = 0; i < 4; i++) {
                    for(let field of fields) {
                        if (!field.conditional) {
                            continue
                        }
                        if(this.editableFields.length && !this.editableFields.includes(field.fieldName)) {
                            continue;
                        }
                        
                        const fieldName = field.conditionalField;
                        const fieldValue = field.conditionalValue;

                        if (fieldName) {
                            if(registration[fieldName] instanceof Array) {
                                if (!registration[fieldName].includes(fieldValue)) {
                                    registration[field.fieldName] = null;
                                }
                            } else if (registration[fieldName] != fieldValue) {
                                registration[field.fieldName] = null;
                            }
                        }
                        
                    }
                }
            });
        }
    },
});
