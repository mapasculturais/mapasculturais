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

        return {
            editableFields,
        }
    },

    computed: {
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

                if (field.registrationProponentTypes?.length && !field.registrationProponentTypes.includes(registration.proponentType)) {
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
});
