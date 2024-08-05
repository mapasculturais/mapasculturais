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
      avaliableEvaluationFields: this.entity.opportunity.avaliableEvaluationFields,
      selectAll: false,
      searchQuery: "",
    };
  },

  computed: {
    filteredFields() {
      const query = this.searchQuery.toLowerCase();
      let fields  = this.getFields();
      return fields.filter(field =>
        field.title.toLowerCase().includes(query) || (field.id && field.id.toString().includes(query))
      );
    }
  },

  methods: {
    fieldSkeleton() {
      let fields = [
        {
          checked: false,
          fieldName: "category",
          title: __("category", "fields-visible-evaluators"),
        },
        {
          checked: false,
          fieldName: "projectName",
          title: __("projectName", "fields-visible-evaluators"),
        },
        {
          checked: false,
          fieldName: "agentsSummary",
          title: __("agentsSummary", "fields-visible-evaluators"),
        },
        {
          checked: false,
          fieldName: "spaceSummary",
          title: __("spaceSummary", "fields-visible-evaluators"),
        },
        ...$MAPAS?.config?.fieldsToEvaluate,
      ];
      console.log(fields);
      return fields;
    },
    getFields() {
      let fields = {...this.fields};
      let avaliableFields = this.entity.opportunity.avaliableEvaluationFields;

      this.fields = Object.values(fields).map(item => {
        item.checked = JSON.parse(avaliableFields[item.fieldName]);
        if (!avaliableFields["category"] && item.categories?.length > 0) {
          item.disabled = true;
          item.titleDisabled = __("activateField", "fields-visible-evaluators");
        }
        
        if(item.fieldName == "field_22"){
          console.log(this.entity.opportunity.avaliableEvaluationFields);
        }

        if (item.conditional && !JSON.parse(avaliableFields[item.conditionalField])) {
          item.disabled = true;
          item.titleDisabled =
            "Para ativar este campo, ative também o campo '" +
            item.conditionalField +
            "'";
        }
        return item;
      });
    },
    toggleSelectAll() {
     this.fields.forEach((field) => {
        if (this.selectAll) {
          if (!field.checked) {
            field.checked = true;
            this.entity.opportunity.avaliableEvaluationFields[field.fieldName] = "true";
          }
        } else {
          if (field.checked) {
            field.checked = false;
            this.entity.opportunity.avaliableEvaluationFields[field.fieldName] = "false";
          }
        }
      });

      this.save();
    },

    toggleSelect(fieldName) {
      this.entity.opportunity.avaliableEvaluationFields[fieldName] = this.entity.opportunity.avaliableEvaluationFields[fieldName] ? "true" : "false";
      this.save();
      this.getFields();
    },
    async save() {
      await this.entity.opportunity.save();
    }
  },
});
