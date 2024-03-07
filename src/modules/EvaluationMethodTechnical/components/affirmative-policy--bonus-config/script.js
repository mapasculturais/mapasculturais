app.component("affirmative-policy--bonus-config", {
  template: $TEMPLATES["affirmative-policy--bonus-config"],

  props: {
    entity: {
      type: Entity,
      required: true,
    },
  },

  setup() {
    // os textos estão localizados no arquivo texts.php deste componente
    const messages = useMessages();
    const text = Utils.getTexts("affirmative-policy--bonus-config");
    return { text, messages };
  },

  data() {
    const config = this.entity.affirmativePolicyBonusConfig || {};
    return {
      totalVacancies: this.entity.opportunity.vacancies ?? 0,
      totalQuota: this.entity.affirmativePolicyBonusConfig
        ? this.entity.affirmativePolicyBonusConfig.vacancies
        : 0,
      pointRewardRoof: this.entity.pointRewardRoof,
      fields: this.entity.opportunity.id
        ? $MAPAS.config.affirmativePolicyBonusConfig.fields[
            this.entity.opportunity.id
          ]
        : [],
      criteria: Object.assign({}, config),
      percent: 0,
    };
  },
  computed: {
    sections() {
      let sections = this.phase.sections.map((section) => {
        const all_criteria = this.phase.criteria;
        section.criteria = [];
        Object.values(all_criteria).forEach((criterion) => {
          if (criterion.sid == section.id) {
            section.criteria.push(criterion);
          }
        });
        return section;
      });

      return sections;
    },
  },
  methods: {
    getField(quota) {
      const id = quota.field ?? quota.fieldName;
      if (Array.isArray(this?.fields)) {
        const field = this?.fields?.find((field) => field.id == id);
        return field;
      } else {
        const fieldsArray = Object.keys(this?.fields).map(
          (id) => this?.fields[id]
        );
        return fieldsArray.find((field) => field.id == id);
      }
    },

    getFieldType(quota) {
      const field = this.getField(quota);
      return field?.fieldType;
    },

    hasField(quota) {
      if (quota?.field === "") return false;
      const field = this.getField(quota);

      return !!field;
    },

    checkCriterionType(criterion, allowedTypes = []) {
      return criterion.selected
        ? !!allowedTypes.includes(criterion.selected.fieldType)
        : false;
    },

    getFieldOptions(quota) {
      const field = this.getField(quota);
      return field?.fieldOptions;
    },

    setFieldName(option, quota) {
      field = this.getField({ field: option.value });
      quota.field = option.value;
      quota.valuesList = field.fieldOptions;
      quota.value = "";
      quota.viewDataValues = field.fieldType;
      this.autoSave();
    },
    checkboxUpdate(event, quota) {
      quota.value =
        typeof quota.value === "object"
          ? {
              ...quota.value,
              [event.target.value]: event.target.checked,
            }
          : {
              [event.target.value]: event.target.checked,
            };
       this.autoSave();
    },

    addConfig() {
      if (!this.entity.pointReward) {
        this.entity.pointReward = [...this.entity.pointReward, {}];
      } else {
        this.entity.pointReward.push({});
      }
    },

    removeConfig(item) {
      this.entity.pointRewardpointReward = this.entity.pointReward.filter(
        function (value, key) {
          return item != key;
        }
      );
      this.distributeQuotas(false);
    },
    autoSave() {
      this.entity.pointRewardRoof = this.pointRewardRoof;
      this.entity.save(3000);
    },
  },

  mounted() {
    if (
      this.entity.affirmativePolicyBonusConfig &&
      this.entity.affirmativePolicyBonusConfig.rules.length > 0
    ) {
      this.updateQuotaPercentage();
    }
  },
});
