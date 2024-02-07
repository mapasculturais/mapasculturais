app.component("fields-visible-evaluators", {
  template: $TEMPLATES["fields-visible-evaluators"],

  props: {
    entity: {
      type: Entity,
      riquered: true,
    },
  },

  setup(props, { slots }) {
    const hasSlot = (name) => !!slots[name];
    const messages = useMessages();
    const text = Utils.getTexts("fields-visible-evaluators");

    return { hasSlot, messages, text };
  },

  beforeCreate() {},
  created() {},

  beforeMount() {},
  mounted() {},

  beforeUpdate() {},
  updated() {},

  beforeUnmount() {},
  unmounted() {},

  data() {
    return {
      avaliableEvaluationFields: {
        ...this.entity.opportunity.avaliableEvaluationFields,
      },
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

      fields.map(function (item) {
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
    save() {
      const resume = Object.keys(this.avaliableEvaluationFields)
        .map((value) => {
          return { name: value, value: this.avaliableEvaluationFields[value] };
        })
        .filter((field) => field.value);

      this.entity.opportunity.avaliableEvaluationFields = Object.fromEntries(
        resume.map((t) => [t.name, t.value])
      );

      this.entity.opportunity.save().then((response) => {
        console.log(response);
      });
    },
  },
});
