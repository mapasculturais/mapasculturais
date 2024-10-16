app.component("fields-visible-evaluators", {
    template: $TEMPLATES["fields-visible-evaluators"],

    props: {
        entity: {
            type: Entity,
            required: true,
        },
    },

    setup(props, { slots }) {
        const hasSlot = (name) => !!slots[name];
        const messages = useMessages();
        const text = Utils.getTexts("fields-visible-evaluators");

        return { hasSlot, messages, text };
    },

    data() {
        return {
            fields: this.fieldSkeleton(),
            avaliableEvaluationFields: {
                ... this.entity.opportunity.avaliableEvaluationFields
            },
            selectAll: false,
            originalFields:[],
            searchQuery: "",
            searchFielter: null,

        };
    },

    mounted() {
        this.getFields();
        this.originalFields = this.fieldSkeleton();
    },

    methods: {
        fieldType(field) {
            let name = field?.fieldName || field.groupName;

            if(name.startsWith('field_')) {
                return 'text'
            }

            return 'file';
        },
        fieldsResult() {
           return this.searchFielter || this.fields
        },
        searchField() {
            const query = this.searchQuery.toLowerCase();

            if (query) {
                this.searchFielter = this.fields.filter(field =>
                    field.title.toLowerCase().includes(query) ||
                    (field.id?.toString().includes(query))
                );
            } else {
                this.searchFielter = null;
            }
        },

        fieldSkeleton() {
            let _fields = [];

            if($MAPAS?.config?.fieldsToEvaluate.length > 0){
                $MAPAS?.config?.fieldsToEvaluate.forEach(item =>{
                    _fields.push(item);
                })
            }

            let fields = [];
            for (const item of _fields) {
                item.checked = false;
                item.disabled = false;
                fields.push(item);
            }

            return fields;
        },
        getFields() {
            let avaliableFields = this.entity.opportunity.avaliableEvaluationFields;

            _fields = Object.values(this.fields).map((item, index) => {
                let field = { ...this.fields[index] }

                field.checked = avaliableFields[item.fieldName] == "true" ? true : false;

                if (avaliableFields["category"] && item.categories?.length > 0) {
                    field.disabled = (avaliableFields["category"] == "true" ? false : true);
                    field.titleDisabled = this.text("activateCategory", "fields-visible-evaluators");
                }

                if (item.conditional) {
                    let condidionalField = this.fields.filter(_item => _item.fieldName == item.conditionalField)
                    field.disabled = (avaliableFields[item.conditionalField] == "true" ? false : true);
                    field.titleDisabled = `${this.text('activateField')} '#${condidionalField[0].id}'`
                }

                this.fields[index] = field;

                if (!field.checked) {
                    this.fields.forEach((_item, pos) => {
                        if (_item.conditionalField &&  _item.conditionalField == field.fieldName) {
                            this.avaliableEvaluationFields[_item.fieldName] = false;
                            this.entity.opportunity.avaliableEvaluationFields[_item.fieldName] = "false"
                        }

                        if (field.fieldName === "category" && _item.categories?.length > 0) {
                            this.avaliableEvaluationFields[_item.fieldName] = false;
                            this.entity.opportunity.avaliableEvaluationFields[_item.fieldName] = "false"
                        }
                    });
                }

            });
        },
        toggleSelectAll() {
            this.fields.forEach((field) => {
                let conditionalField = this.avaliableEvaluationFields[field.conditionalField] || this.fields.filter((conditionalField) => conditionalField.fieldName == field.conditionalField);
                
                if (this.selectAll) {
                    if (!field.checked) {
                        field.checked = true;
                        this.avaliableEvaluationFields[field.fieldName] = "true";

                        if (field.conditional) {
                            field.disabled = conditionalField.checked ? true : false;
                        }
                    }
                } else {
                    if (field.checked) {
                        field.checked = false;
                        this.avaliableEvaluationFields[field.fieldName] = "false";

                        if (field.conditional) {
                            field.disabled = conditionalField.checked ? false : true;
                        }
                    }
                }
            });
            this.entity.opportunity.avaliableEvaluationFields = this.avaliableEvaluationFields;
            this.save();
        },

        toggleSelect(fieldName) {
            if(Array.isArray(this.entity.opportunity.avaliableEvaluationFields) && this.entity.opportunity.avaliableEvaluationFields.length == 0) {
                this.entity.opportunity.avaliableEvaluationFields = {};
            }

            this.entity.opportunity.avaliableEvaluationFields[fieldName] = this.avaliableEvaluationFields[fieldName] ? "true" : "false";
            this.save();
            this.getFields();
        },
        async save() {
            await this.entity.opportunity.save();
        }
    },
});
