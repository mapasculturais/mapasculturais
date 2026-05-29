app.component("affirmative-policy--bonus-config", {
  template: $TEMPLATES["affirmative-policy--bonus-config"],

  props: {
    entity: {
      type: Entity,
      required: true,
    },
  },

  setup() {
    const messages = useMessages();
    const text = Utils.getTexts("affirmative-policy--bonus-config");
    return { text, messages };
  },

  data() {
    const normalized = this._normalizeConfig(this.entity.pointReward);
    return {
      fields: this.entity.opportunity.id
        ? $MAPAS.config.affirmativePolicyBonusConfig.fields[this.entity.opportunity.id]
        : [],
      bonusType: normalized.type,
      pendingBonusType: null,
      normalizedRules: normalized.rules,
    };
  },

  computed: {
    hasRules() {
      return this.normalizedRules && this.normalizedRules.length > 0;
    },
  },

  methods: {
    // ----------------------------------------------------------------
    // Normalização (espelha a lógica PHP para uso local)
    // ----------------------------------------------------------------

    _normalizeConfig(config) {
      const empty = { type: "percentage", rules: [] };

      if (!config) return empty;

      // Formato legado: array de regras
      if (Array.isArray(config)) {
        return {
          type: "percentage",
          rules: config.map((rule) => this._normalizeRule(rule)),
        };
      }

      if (typeof config !== "object") return empty;

      const type = config.type === "fixed" ? "fixed" : "percentage";
      const rules = Array.isArray(config.rules)
        ? config.rules.map((rule) => this._normalizeRule(rule))
        : [];

      return { type, rules };
    },

    _normalizeRule(rule) {
      const r = { ...rule };
      // Deriva bonusValue de fieldPercent quando ausente (legado)
      if (r.bonusValue === undefined && r.fieldPercent !== undefined) {
        r.bonusValue = r.fieldPercent;
      }
      if (r.bonusValue === undefined) {
        r.bonusValue = 0;
      }
      if (r.value === undefined) {
        r.value = {};
      }
      return r;
    },

    // ----------------------------------------------------------------
    // Serialização para salvar no entity.pointReward
    // ----------------------------------------------------------------

    _serializeConfig() {
      return {
        type: this.bonusType,
        rules: (this.normalizedRules || []).map((rule) => {
          const r = {
            field: rule.field,
            value: rule.value,
            bonusValue: rule.bonusValue ?? 0,
          };
          if (rule.fieldName !== undefined) r.fieldName = rule.fieldName;
          if (rule.valuesList !== undefined) r.valuesList = rule.valuesList;
          if (rule.viewDataValues !== undefined) r.viewDataValues = rule.viewDataValues;
          if (rule.eligibleValues !== undefined) r.eligibleValues = rule.eligibleValues;
          return r;
        }),
      };
    },

    // ----------------------------------------------------------------
    // Troca de tipo com confirmação quando há regras cadastradas
    // ----------------------------------------------------------------

    onTypeChange(event, openConfirm) {
      const selectedType = event.target.value;

      if (selectedType === this.bonusType) {
        return;
      }

      if (!this.hasRules) {
        this.bonusType = selectedType;
        this._syncAndSave();
        return;
      }

      this.pendingBonusType = selectedType;
      event.target.value = this.bonusType;
      openConfirm();
    },

    confirmTypeChange() {
      if (this.pendingBonusType) {
        this.bonusType = this.pendingBonusType;
        this.pendingBonusType = null;
      }

      this._syncAndSave();
    },

    cancelTypeChange() {
      this.pendingBonusType = null;
    },

    // ----------------------------------------------------------------
    // Campos
    // ----------------------------------------------------------------

    getField(quota) {
      const id = quota.field ?? quota.fieldName;
      if (Array.isArray(this?.fields)) {
        return this?.fields?.find((field) => field.id == id);
      }
      const fieldsArray = Object.keys(this?.fields).map((id) => this?.fields[id]);
      return fieldsArray.find((field) => field.id == id);
    },

    getFieldType(quota) {
      return this.getField(quota)?.fieldType;
    },

    hasField(quota) {
      if (quota?.field === "") return false;
      return !!this.getField(quota);
    },

    getFieldOptions(quota) {
      return this.getField(quota)?.fieldOptions;
    },

    setFieldName(option, quota) {
      const field = this.getField({ field: option.value });
      quota.field = option.value;
      quota.valuesList = field.fieldOptions;
      quota.value = {};
      quota.viewDataValues = field.fieldType;
    },

    checkboxUpdate(event, quota) {
      if (event.target.checked) {
        quota.value =
          typeof quota.value === "object"
            ? { ...quota.value, [event.target.value]: String(event.target.checked) }
            : { [event.target.value]: String(event.target.checked) };
      } else {
        delete quota.value[event.target.value];
      }
    },

    optionValue(option) {
      return option.split(":")[0];
    },

    optionLabel(option) {
      const parts = option.split(":");
      return parts.length > 1 ? parts[1] : parts[0];
    },

    // ----------------------------------------------------------------
    // Adicionar / remover regras
    // ----------------------------------------------------------------

    addConfig() {
      if (this.entity.opportunity.affirmativePoliciesEligibleFields.length === 0) {
        this.messages.error(this.text("emptyAffimativePolicies"));
        return;
      }

      if (!this.normalizedRules) {
        this.normalizedRules = [];
      }
      this.normalizedRules.push({ field: "", value: {}, bonusValue: 0 });

      if (!this.entity.isActivePointReward) {
        this.entity.isActivePointReward = true;
      }

      this._syncAndSave();
    },

    removeConfig(index) {
      this.normalizedRules = this.normalizedRules.filter((_, key) => key !== index);
      if (!this.normalizedRules.length) {
        this.entity.isActivePointReward = false;
      }
      this._syncAndSave(true, 200);
    },

    // ----------------------------------------------------------------
    // Sincronização e autosave
    // ----------------------------------------------------------------

    _syncAndSave(updated = false, time = 3000) {
      this.entity.pointReward = this._serializeConfig();
      this.autoSave(updated, time);
    },

    autoSave(updated = false, time = 3000) {
      const filled = (this.normalizedRules || []).filter(
        (rule) =>
          rule.field !== undefined &&
          rule.field !== "" &&
          (rule.value !== "" || rule.eligibleValues !== undefined) &&
          (rule.value || rule.eligibleValues) &&
          rule.bonusValue !== undefined &&
          rule.bonusValue !== null
      );

      if (filled.length || updated) {
        this.entity.save(time);
      }
    },
  },

  watch: {
    normalizedRules: {
      deep: true,
      handler() {
        this._syncAndSave(true, 500);
      },
    },
  },
});
