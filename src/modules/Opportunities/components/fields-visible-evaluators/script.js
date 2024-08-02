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
      avaliableEvaluationFields: {
        ...this.entity.opportunity.avaliableEvaluationFields,
      },
      selectAll: false,
    };
  },

  computed: {
    fields() {
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

      let avaliableFields = $MAPAS.requestedEntity.avaliableEvaluationFields;

      fields.forEach((item) => {
        item.checked = !!avaliableFields[item.fieldName];

        if (!avaliableFields["category"] && item.categories?.length > 0) {
          item.disabled = true;
          item.titleDisabled = __("activateField", "fields-visible-evaluators");
        }

        if (item.conditional && !avaliableFields[item.conditionalField]) {
          item.disabled = true;
          item.titleDisabled =
            "Para ativar este campo, ative também o campo '" +
            item.conditionalField +
            "'";
        }
      });

      return fields;
    },
  },

  methods: {
    toggleSelectAll() {
      this.fields.forEach((field) => {
        if (this.selectAll) {
          if (!field.checked) {
            field.checked = true;
            this.avaliableEvaluationFields[field.fieldName] = "true";
          }

        } else {
          if (field.checked) {
            field.checked = false;
            this.avaliableEvaluationFields[field.fieldName] = "false";
          }
        }
      });

      this.entity.opportunity.avaliableEvaluationFields = this.avaliableEvaluationFields;
      this.save();
    },

    toggleSelect(fieldName) {
      this.entity.opportunity.avaliableEvaluationFields[fieldName] = this.avaliableEvaluationFields[fieldName] ? "true" : "false";
      this.save();
    },
    async save() {
      await this.entity.opportunity.save();
    }
  },
});
