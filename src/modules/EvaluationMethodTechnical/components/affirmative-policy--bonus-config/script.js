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
      const id = quota.field;

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
      if (quota?.fieldName === "") return false;
      const field = this.getField(quota);
      return !!field;
    },

    setCriterion(option, id) {
      const field = Object.values(this.fields).filter(
        (field) => field.fieldName == option.value
      );
      this.criteria[id].selected = !!field.length ? field[0] : null;
      this.criteria[id].criterionType = option.value;
      this.criteria[id].preferences = this.checkCriterionType(
        this.criteria[id],
        ["checkboxes", "select"]
      )
        ? []
        : null;
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
      quota.fieldName = option.value;
    },

    addConfig() {
      if (!this.entity.affirmativePolicyBonusConfig) {
        this.entity.affirmativePolicyBonusConfig = {
          vacancies: 0,
          rules: [this.skeleton()],
        };
      } else {
        this.entity.affirmativePolicyBonusConfig.rules.push(this.skeleton());
      }
    },
    skeleton() {
      const rules = {
        fieldName: "",
        vacancies: 0,
        eligibleValues: [],
      };
      return rules;
    },
    removeConfig(item) {
      this.entity.affirmativePolicyBonusConfig.rules =
        this.entity.affirmativePolicyBonusConfig.rules.filter(function (
          value,
          key
        ) {
          return item != key;
        });
      this.distributeQuotas(false);
    },
    autoSave() {
      var date = new Date();
      var new_id = "p-" + date.getTime();
      this.entity.pointRewardRoof = this.pointRewardRoof;
      /*  this.entity.pointReward = this.entity.affirmativePolicyBonusConfig.map(
        (quota) => {
          return { id: new_id, fieldPercent: 0, field: "", value: "" };
        }
      ); */
      this.entity.save(3000);
    },
    updateTotalQuotas() {
      this.totalQuota = (this.totalVacancies * this.totalPercentage) / 100;
      this.entity.affirmativePolicyBonusConfig.vacancies = this.totalQuota;
    },
    updateQuotaPercentage() {
      this.totalPercentage = (this.totalQuota * 100) / this.totalVacancies;
      this.entity.affirmativePolicyBonusConfig.vacancies = this.totalQuota;
    },
    updateRuleQuotas(quota) {
      quota.vacancies = (this.totalQuota * quota.percentage) / 100;
      this.distributeQuotas();
    },
    updateRuleQuotaPercentage(quota, load = false) {
      quota.percentage = (quota.vacancies * 100) / this.totalVacancies;
      this.distributeQuotas(load);
    },
    distributeQuotas(load) {
      let countVacancies = 0;
      if (
        this.entity.affirmativePolicyBonusConfig &&
        this.entity.affirmativePolicyBonusConfig.rules.length > 0
      ) {
        this.entity.affirmativePolicyBonusConfig.rules.forEach(
          (quota, index) => {
            countVacancies += quota.vacancies;
          }
        );
        this.totalQuota = countVacancies;

        if (this.totalQuota > this.totalVacancies) {
          this.messages.error(this.text("limitQuota"));
        } else {
          this.updateQuotaPercentage();
          if (!load) {
            this.autoSave();
          }
        }
      }
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
