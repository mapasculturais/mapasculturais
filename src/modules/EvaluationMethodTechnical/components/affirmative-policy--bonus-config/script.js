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

  updated () {
    this.autoSave(true, 200);
  },

  data() {
    const config = this.entity.affirmativePolicyBonusConfig || {};
    return {
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
    },
    checkboxUpdate(event, quota) {
      if (event.target.checked) {
        quota.value =
          typeof quota.value === "object"
            ? {
                ...quota.value,
                [event.target.value]: String(event.target.checked),
              }
            : {
                [event.target.value]: String(event.target.checked),
              };
      } else {
        delete quota.value[event.target.value];
      }
    },

    addConfig() {
      if(this.entity.opportunity.affirmativePoliciesEligibleFields.length == 0) {
        this.messages.error(this.text('emptyAffimativePolicies'));
        return;
      }

      if (!this.entity.pointReward) {
        this.entity.pointReward = [{}];
      } else {
        this.entity.pointReward.push({});
      }

      if (!this.entity.isActivePointReward) {
        this.entity.isActivePointReward = true;
      }
    },

    removeConfig(item) {
      this.entity.pointReward = this.entity.pointReward.filter(function (
        value,
        key
      ) {
        return item != key;
      });
      if (!this.entity.pointReward.length) {
        this.entity.isActivePointReward = false;
      }
      this.autoSave(true, 200);
    },

    optionValue(option) {
      let _option = option.split(':');
      return _option[0];
    },

    optionLabel(option) {
      let _option = option.split(':');
      return _option.length > 1 ? _option[1] : _option[0];
    },

    autoSave(updated = false, time = 3000) {
      const filled = Object.values(this.entity.pointReward).filter(
        pointReward => {
            return pointReward.field !== undefined 
                && pointReward.field 
                && (pointReward.value !== "" || pointReward.eligibleValues !== undefined)
                && (pointReward.value || pointReward.eligibleValues)
                && pointReward.fieldPercent !== undefined
                && pointReward.fieldPercent 
        }
      );
      
      if(filled.length || updated) {
        this.entity.save(time);
      }
    },
  },
});
