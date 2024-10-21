app.component('registration-form', {
    template: $TEMPLATES['registration-form'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-form')
        return { text, hasSlot }
    },



    mounted() {
        const registration = this.registration;
        const self = this;
        globalThis.addEventListener('afterFetch', ({detail}) => {
            if(registration.singleUrl == detail.url) {
                self.category = Vue.readonly(self.registration.category);
                self.proponentType = Vue.readonly(self.registration.proponentType);
                self.range = Vue.readonly(self.registration.range);
            }
        })
    },

    data() {
        
        const category = Vue.readonly(this.registration.category);
        const proponentType = Vue.readonly(this.registration.proponentType);
        const range = Vue.readonly(this.registration.range);

        const hasCategory = this.registration.opportunity.registrationCategories?.length > 0;
        const hasProponentType = this.registration.opportunity.registrationProponentTypes?.length > 0;
        const hasRange = this.registration.opportunity.registrationRanges?.length > 0;

        return {
            category,
            proponentType,
            range,
            hasCategory,
            hasProponentType,
            hasRange,
        }
    },

    computed: {
        fields() {
            const registration = this.registration;

            let fields = [...$MAPAS.config.registrationForm.fields, ...$MAPAS.config.registrationForm.files];

            fields = fields.sort((a,b) => a.displayOrder - b.displayOrder);

            const result = [];

            for(let field of fields) {
                if(field.categories?.length && !field.categories.includes(registration.category)) {
                    continue;
                }

                if(field.registrationRanges?.length && !field.registrationRanges.includes(registration.range)) {
                    continue;
                }

                if(field.registrationProponentTypes?.length && !field.registrationProponentTypes.includes(registration.proponentType)) {
                    continue;
                }

                if(field.conditional) {
                    const fieldName = field.conditionalField;
                    const fieldValue = field.conditionalValue;

                    if(fieldName) {
                        if(registration[fieldName] != fieldValue) {
                            continue
                        }
                    }
                }

                result.push(field);
            }

            return result;
        },

        sections() {
            const sectionSkel = {
                id: '',
                title: '',
                description: '',
            }
            const sections = [];

            let currentSection = {...sectionSkel, fields:[]};
            for(let field of this.fields) {
                if(field.fieldType == 'section') {
                    currentSection = {...sectionSkel, fields:[]};
                    sections.push(currentSection);

                    currentSection.id = field.fieldName;
                    currentSection.title = field.title;
                    currentSection.description = field.description;
                    continue;
                }

                // se o primeiro campo do formulário não é uma seção será "vazia"
                if(sections.length === 0) {
                    sections.push(currentSection);
                }

                currentSection.fields.push(field);
            }

            return sections;
        }
    },
    
    
    methods: {
        isValid() {
            let valid = true;

            if(this.registration.opportunity.registrationCategories?.length > 0 && !this.category) {
                valid = false;
            }

            if(this.registration.opportunity.registrationProponentTypes?.length > 0 && !this.proponentType) {
                valid = false;
            }

            if(this.registration.opportunity.registrationRanges?.length > 0 && !this.range) {
                valid = false;
            }

            return valid;
        }
    },
});
