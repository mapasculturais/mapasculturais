app.component('registration-distribution-rule', {
    template: $TEMPLATES['registration-distribution-rule'],
    emits: ['update:modelValue', 'update:parentFilters'],

    props: {
        opportunity: {
            type: Entity,
            required: true
        },
        modelValue: {
            type: Object,
            default: () => ({
                categories: [],
                proponentTypes: [],
                ranges: [],
                distribution: '',
                sentTimestamp: { from: '', to: '' },
                fields: {}
            })
        },
        parentFilters: {
            type: Object,
            default: null
        },
        disableFilters: {
            type: [Array, Object],
            default: () => []
        },
        enableFilterByNumber: {
            type: Boolean,
            default: false
        },
        enableFilterBySentTimestamp: {
            type: Boolean,
            default: false
        },
        titleModal: {
            type: String,
            default: ''
        }
    },

    setup() {
        const text = Utils.getTexts('registration-distribution-rule');
        return { text };
    },

    data() {
        return {
            selectedField: '',
            selectedConfigs: [],
            selectedDistribution: '',
            localModel: this.normalizeModel(this.modelValue)
        };
    },

    computed: {
        phase() {
            const opp = this.opportunity;

            if (!opp) {
                return null;
            }

            const first = opp.firstPhase || opp;
            const phases = first.phases || (Array.isArray(first) ? first : [first]);

            return phases[0] || first;
        },

        registrationCategories() {
            return this.phase?.registrationCategories ?? [];
        },

        registrationProponentTypes() {
            return this.phase?.registrationProponentTypes ?? [];
        },

        registrationRanges() {
            const ranges = this.phase?.registrationRanges ?? [];
            return Array.isArray(ranges) ? ranges.map(range => (typeof range === 'object' && range?.label != null ? range.label : range)) : [];
        },

        selectionFields() {
            const opp = this.opportunity;
            const allowed = ['select', 'checkboxes', 'checkbox'];
            const byId = {};

            const fromConfigs = (configs) => {
                (configs || []).forEach(field => {
                    if (allowed.includes(field.fieldType) && field.fieldName) {
                        const id = String(field.id ?? field.fieldName);
                        byId[id] = {
                            fieldName: field.fieldName,
                            title: field.title || field.fieldName,
                            fieldType: field.fieldType || 'select',
                            fieldOptions: field.fieldOptions || []
                        };
                    }
                });
            };

            const configs = opp?.registrationFieldConfigurations;

            if (configs && configs.length) {
                fromConfigs(configs);
            }

            if (typeof $MAPAS != 'undefined' && $MAPAS.config?.registrationFilterFields) {
                const filterFields = $MAPAS.config.registrationFilterFields;
                filterFields.forEach(field => {
                    if (!field) {
                        return;
                    }

                    const id = String(field.fieldName || field.id);

                    if (byId[id]) {
                        return;
                    }

                    byId[id] = {
                        fieldName: field.fieldName || id,
                        title: field.title || field.fieldName || id,
                        fieldType: field.fieldType || 'select',
                        fieldOptions: field.fieldOptions || []
                    };
                });
            }

            return byId;
        },

        selectedFieldType() {
            if (!this.selectedField) {
                return '';
            }

            return this.selectedField.startsWith('field:') ? 'field' : this.selectedField;
        },

        selectedFieldId() {
            if (this.selectedFieldType !== 'field') {
                return null;
            }

            return this.selectedField.replace(/^field:/, '');
        },

        filteredCategories() {
            let list = this.registrationCategories;
            
            if (this.hasCommissionFilters && this.parentFilters && Array.isArray(this.parentFilters.categories) && this.parentFilters.categories.length > 0) {
                list = list.filter(cat => this.parentFilters.categories.includes(cat));
            }
 
            const disabled = this.disabledValues.categories || [];

            return list.filter(cat => !disabled.includes(cat));
        },

        filteredProponentTypes() {
            let list = this.registrationProponentTypes;
            
            if (this.hasCommissionFilters && this.parentFilters && Array.isArray(this.parentFilters.proponentTypes) && this.parentFilters.proponentTypes.length > 0) {
                list = list.filter(type => this.parentFilters.proponentTypes.includes(type));
            }

            const disabled = this.disabledValues.proponentTypes || [];
  
            return list.filter(type => !disabled.includes(type));
        },

        filteredRanges() {
            let list = this.registrationRanges;
            
            if (this.hasCommissionFilters && this.parentFilters && Array.isArray(this.parentFilters.ranges) && this.parentFilters.ranges.length > 0) {
                list = list.filter(range => this.parentFilters.ranges.includes(range));
            }

            const disabled = this.disabledValues.ranges || [];

            return list.filter(range => !disabled.includes(range));
        },

        canConfirm() {
            if (this.selectedField === 'sentTimestamp') {
                const from = this.selectedConfigs?.from;
                const to = this.selectedConfigs?.to;

                if (!from && !to) {
                    return false;
                }

                if (from && to && new Date(to) < new Date(from)) {
                    return false;
                }
            }

            return true;
        },

        sentTimestampErrors() {
            if (this.selectedField !== 'sentTimestamp') {
                return { from: '', to: '' };
            }

            const from = this.selectedConfigs?.from;
            const to = this.selectedConfigs?.to;

            if (from && to && new Date(from) > new Date(to)) {
                return { from: this.text('A data inicial não pode ser maior que a data final'), to: '' };
            }

            return { from: '', to: '' };
        },

        tagsList() {
            return this.buildTagsList();
        },

        tagsLabels() {
            return this.buildTagsLabels();
        },

        disabledTypes() {
            return Array.isArray(this.disableFilters) ? this.disableFilters : [];
        },

        disabledValues() {
            return Array.isArray(this.disableFilters) ? {} : (this.disableFilters || {});
        },

        /** Verifica se a comissão tem pelo menos um filtro configurado */
        hasCommissionFilters() {
            if (!this.parentFilters || typeof this.parentFilters !== 'object') {
                return false;
            }

            if (Array.isArray(this.parentFilters.categories) && this.parentFilters.categories.length > 0) {
                return true;
            }

            if (Array.isArray(this.parentFilters.proponentTypes) && this.parentFilters.proponentTypes.length > 0) {
                return true;
            }

            if (Array.isArray(this.parentFilters.ranges) && this.parentFilters.ranges.length > 0) {
                return true;
            }

            if (this.parentFilters.fields && typeof this.parentFilters.fields === 'object' && Object.keys(this.parentFilters.fields).length > 0) {
                return true;
            }

            if (this.parentFilters.distribution && typeof this.parentFilters.distribution === 'string' && this.parentFilters.distribution.trim()) {
                return true;
            }

            if (this.parentFilters.sentTimestamp && typeof this.parentFilters.sentTimestamp === 'object' && (this.parentFilters.sentTimestamp.from || this.parentFilters.sentTimestamp.to)) {
                return true;
            }

            return false;
        },

        availableFields() {
            if (!this.isFilterAvailable('fields')) {
                return {};
            }

            const available = {};
            Object.entries(this.selectionFields).forEach(([fieldId, field]) => {
                if (this.isFieldAllowedByParent(fieldId)) {
                    available[fieldId] = field;
                }
            });

            return available;
        }
    },

    watch: {
        modelValue: {
            handler(val) {
                this.localModel = this.normalizeModel(val);
            },
            deep: true
        },
        parentFilters: {
            handler(newVal) {
                this.applyParentRestrictions();
                this.$emit('update:parentFilters', newVal ?? null);
            },
            deep: true
        },
        disableFilters: {
            handler() {
                this.applyParentRestrictions();
            },
            deep: true
        }
    },

    mounted() {
        this.localModel = this.normalizeModel(this.modelValue);
        this.applyParentRestrictions();
        
        if (this.parentFilters != null) {
            this.$emit('update:parentFilters', this.parentFilters);
        }
    },

    methods: {
        normalizeModel(val) {
            const model = val || {};

            return {
                categories: Array.isArray(model.categories) ? [...model.categories] : [],
                proponentTypes: Array.isArray(model.proponentTypes) ? [...model.proponentTypes] : [],
                ranges: Array.isArray(model.ranges) ? [...model.ranges] : [],
                distribution: typeof model.distribution == 'string' ? model.distribution : '',
                sentTimestamp: {
                    from: model.sentTimestamp?.from ?? '',
                    to: model.sentTimestamp?.to ?? ''
                },
                fields: model.fields && typeof model.fields == 'object' ? { ...model.fields } : {}
            };
        },

        applyParentRestrictions() {
            if (!this.localModel) {
                return;
            }
            
            const next = { ...this.normalizeModel(this.localModel) };
            let changed = false;
            const disabled = this.disabledValues;

            if (this.parentFilters && Array.isArray(this.parentFilters.categories) && this.parentFilters.categories.length > 0) {
                const allowed = new Set(this.parentFilters.categories);
                const disabledCategories = disabled.categories || [];
                const prev = next.categories.length;
                next.categories = next.categories.filter(c => {
                    return allowed.has(c) && disabledCategories.indexOf(c) === -1;
                });
                
                if (next.categories.length != prev) {
                    changed = true;
                }
            } else if (Array.isArray(disabled.categories) && disabled.categories.length > 0) {
                const prev = next.categories.length;
                next.categories = next.categories.filter(c => disabled.categories.indexOf(c) === -1);
                
                if (next.categories.length != prev) {
                    changed = true;
                }
            }

            if (this.parentFilters && Array.isArray(this.parentFilters.proponentTypes) && this.parentFilters.proponentTypes.length > 0) {
                const allowed = new Set(this.parentFilters.proponentTypes);
                const disabledProponentTypes = disabled.proponentTypes || [];
                const prev = next.proponentTypes.length;
                next.proponentTypes = next.proponentTypes.filter(p => {
                    return allowed.has(p) && disabledProponentTypes.indexOf(p) === -1;
                });
                
                if (next.proponentTypes.length != prev) {
                    changed = true;
                }
            } else if (Array.isArray(disabled.proponentTypes) && disabled.proponentTypes.length > 0) {
                const prev = next.proponentTypes.length;
                next.proponentTypes = next.proponentTypes.filter(p => disabled.proponentTypes.indexOf(p) === -1);
                
                if (next.proponentTypes.length != prev) {
                    changed = true;
                }
            }

            if (this.parentFilters && Array.isArray(this.parentFilters.ranges) && this.parentFilters.ranges.length > 0) {
                const allowed = new Set(this.parentFilters.ranges);
                const disabledRanges = disabled.ranges || [];
                const prev = next.ranges.length;
                next.ranges = next.ranges.filter(r => {
                    return allowed.has(r) && disabledRanges.indexOf(r) === -1;
                });
                
                if (next.ranges.length != prev) {
                    changed = true;
                }
            } else if (Array.isArray(disabled.ranges) && disabled.ranges.length > 0) {
                const prev = next.ranges.length;
                next.ranges = next.ranges.filter(r => disabled.ranges.indexOf(r) === -1);
                
                if (next.ranges.length != prev) {
                    changed = true;
                }
            }

            if (this.parentFilters && this.parentFilters.fields && typeof this.parentFilters.fields === 'object') {
                const nextFields = {};
                const disabledFields = disabled.fields || {};

                for (const [fieldId, allowedValues] of Object.entries(this.parentFilters.fields)) {
                    if (!Array.isArray(allowedValues) || allowedValues.length === 0) {
                        continue;
                    }

                    const allowedSet = new Set(allowedValues);
                    const disabledForField = disabledFields[fieldId] || [];
                    const disabledSet = new Set(disabledForField);
                    const current = next.fields[fieldId];
                    
                    if (Array.isArray(current)) {
                        const filtered = current.filter(v => {
                            return allowedSet.has(v) && !disabledSet.has(v);
                        });
                        
                        if (filtered.length > 0) {
                            nextFields[fieldId] = filtered;
                        }
                    }
                }

                const prevKeys = Object.keys(next.fields).sort().join('');
                const nextKeys = Object.keys(nextFields).sort().join('');

                if (prevKeys != nextKeys) {
                    changed = true;
                } else {
                    for (const k of Object.keys(next.fields)) {
                        const a = (next.fields[k] || []).slice().sort().join();
                        const b = (nextFields[k] || []).slice().sort().join();
                        
                        if (a != b) {
                            changed = true;
                            break;
                        }
                    }
                }
                next.fields = nextFields;
            } else if (disabled.fields && typeof disabled.fields === 'object') {
                const nextFields = {};
                const disabledFields = disabled.fields;

                Object.entries(next.fields || {}).forEach(([fieldId, values]) => {
                    if (!Array.isArray(values)) {
                        return;
                    }

                    const disabledForField = disabledFields[fieldId] || [];
                    const filtered = values.filter(v => disabledForField.indexOf(v) === -1);
                    
                    if (filtered.length > 0) {
                        nextFields[fieldId] = filtered;
                    }
                });

                const prevKeys = Object.keys(next.fields).sort().join('');
                const nextKeys = Object.keys(nextFields).sort().join('');

                if (prevKeys != nextKeys) {
                    changed = true;
                } else {
                    for (const k of Object.keys(next.fields)) {
                        const a = (next.fields[k] || []).slice().sort().join();
                        const b = (nextFields[k] || []).slice().sort().join();
                        
                        if (a != b) {
                            changed = true;
                            break;
                        }
                    }
                }
                next.fields = nextFields;
            }

            if (changed) {
                this.localModel = next;
                this.$emit('update:modelValue', next);
            }
        },

        isFilterAvailable(type) {
            if (this.disabledTypes.includes(type)) {
                return false;
            }

            if (type === 'categories') {
                if (this.registrationCategories.length === 0) {
                    return false;
                }

                if (this.hasCommissionFilters && this.parentFilters && Array.isArray(this.parentFilters.categories) && this.parentFilters.categories.length > 0) {
                    return this.parentFilters.categories.length > 1;
                }

                return this.filteredCategories.length > 0;
            }

            if (type === 'proponentTypes') {
                if (this.registrationProponentTypes.length === 0) {
                    return false;
                }
                
                if (this.hasCommissionFilters && this.parentFilters && Array.isArray(this.parentFilters.proponentTypes) && this.parentFilters.proponentTypes.length > 0) {
                    return this.parentFilters.proponentTypes.length > 1;
                }

                return this.filteredProponentTypes.length > 0;
            }

            if (type === 'ranges') {
                if (this.registrationRanges.length === 0) {
                    return false;
                }
                
                if (this.hasCommissionFilters && this.parentFilters && Array.isArray(this.parentFilters.ranges) && this.parentFilters.ranges.length > 0) {
                    return this.parentFilters.ranges.length > 1;
                }

                return this.filteredRanges.length > 0;
            }

            if (type === 'distribution') {
                return this.enableFilterByNumber;
            }

            if (type === 'sentTimestamp') {
                return this.enableFilterBySentTimestamp;
            }

            if (type === 'fields') {
                if (Object.keys(this.selectionFields).length === 0) {
                    return false;
                }

                if (this.hasCommissionFilters && this.parentFilters && this.parentFilters.fields && typeof this.parentFilters.fields === 'object') {
                    const fieldsKeys = Object.keys(this.parentFilters.fields);
                    if (fieldsKeys.length > 0) {
                        const hasFields = fieldsKeys.some(id => {
                            const arr = this.parentFilters.fields[id];
                            return Array.isArray(arr) && arr.length > 0;
                        });
                        
                        if (hasFields) {
                            return true;
                        }
                    }
                }
                
                return Object.keys(this.selectionFields).some(fieldId => {
                    return this.getFieldOptions(fieldId).length > 0;
                });
            }

            return false;
        },

        isFilterDisabled(type) {
            if (this.disabledTypes.includes(type)) {
                return true;
            }

            return false;
        },

        isFieldAllowedByParent(fieldId) {
            if (this.hasCommissionFilters && this.parentFilters?.fields && typeof this.parentFilters.fields === 'object') {
                const allowed = this.parentFilters.fields[fieldId];

                if (Array.isArray(allowed) && allowed.length > 0) {
                    return true;
                }
            }

            return this.getFieldOptions(fieldId).length > 0;
        },

        getFieldOptions(fieldId) {
            const field = this.selectionFields[fieldId];
            if (!field) {
                return [];
            }

            let options = [];

            if (field.fieldType === 'checkbox') {
                const parentOptsRaw = this.parentFilters?.fields?.[fieldId];
                
                if (Array.isArray(parentOptsRaw) && parentOptsRaw.length > 0) {
                    let hasTrue = false;
                    let hasFalse = false;
                    
                    parentOptsRaw.forEach((val) => {
                        if (val === true || val === 'true' || val === 1 || val === '1' || val === 'on') {
                            hasTrue = true;
                        } else if (val === false || val === 'false' || val === 0 || val === '0' || val === '' || val == null) {
                            hasFalse = true;
                        } else {
                            hasTrue = true;
                        }
                    });

                    options = [];
                    if (hasTrue) {
                        options.push(true);
                    }
                    if (hasFalse) {
                        options.push(false);
                    }
                    
                    return options;
                } else {
                    options = [true, false];
                }
            } else {
                options = field.fieldOptions || [];

                if (!options.length) {
                    return [];
                }

                const parentOpts = this.parentFilters?.fields?.[fieldId];
                if (Array.isArray(parentOpts) && parentOpts.length > 0) {
                    options = options.filter(option => parentOpts.includes(option));
                    return options;
                }
            }

            const disabledValuesForField = this.disabledValues.fields?.[fieldId] || [];
            if (Array.isArray(disabledValuesForField) && disabledValuesForField.length > 0) {
                options = options.filter(option => disabledValuesForField.indexOf(option) === -1);
            }

            return options;
        },

        getFieldOptionLabel(fieldId, option) {
            const field = this.selectionFields[fieldId];

            if (field?.fieldType == 'checkbox') {
                if (option == true) {
                    return this.text('Marcado');
                }

                if (option == false) {
                    return this.text('Desmarcado');
                }
            }

            return String(option);
        },

        handleSelection() {
            const field = this.selectedField;
            const model = this.localModel;

            if (field == 'sentTimestamp') {
                this.selectedConfigs = {
                    from: model.sentTimestamp?.from ?? '',
                    to: model.sentTimestamp?.to ?? ''
                };
                return;
            }

            if (field == 'distribution') {
                this.selectedDistribution = model.distribution ?? '';
                return;
            }

            if (field.startsWith('field:')) {
                const fieldId = field.replace(/^field:/, '');
                this.selectedConfigs = Array.isArray(model.fields?.[fieldId]) ? [...model.fields[fieldId]] : [];
                return;
            }

            this.selectedConfigs = Array.isArray(model[field]) ? [...model[field]] : [];
        },

        addConfig(modal) {
            const field = this.selectedField;
            const next = { ...this.normalizeModel(this.localModel) };

            if (field == 'sentTimestamp') {
                const from = this.selectedConfigs?.from;
                const to = this.selectedConfigs?.to;

                if (!from && !to) {
                    modal.close();
                    return;
                }

                if (from && to && new Date(to) < new Date(from)) {
                    modal.close();
                    return;
                }

                next.sentTimestamp = {
                    from: from ? this.formatSentTimestamp(from) : '',
                    to: to ? this.formatSentTimestamp(to) : ''
                };
            }

            if (field == 'distribution') {
                next.distribution = this.selectedDistribution?.trim() ?? '';
            }

            if (field.startsWith('field:')) {
                const fieldId = field.replace(/^field:/, '');
                if (!next.fields) {
                    next.fields = {};
                }
                next.fields[fieldId] = Array.isArray(this.selectedConfigs) ? [...this.selectedConfigs] : [];
            }

            if (['categories', 'proponentTypes', 'ranges'].includes(field)) {
                next[field] = Array.isArray(this.selectedConfigs) ? [...this.selectedConfigs] : [];
            }

            this.localModel = next;
            this.$emit('update:modelValue', next);
            this.selectedField = '';
            this.selectedConfigs = [];
            this.selectedDistribution = '';
            modal.close();
        },

        formatSentTimestamp(dateString) {
            if (!dateString) {
                return '';
            }

            try {
                const date = new Date(dateString);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const seconds = String(date.getSeconds()).padStart(2, '0');

                return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
            } catch (error) {
                return dateString;
            }
        },

        formatDateDisplay(dateString) {
            if (!dateString) {
                return '';
            }

            try {
                const date = new Date(dateString);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();

                return `${day}/${month}/${year}`;
            } catch (error) {
                return dateString;
            }
        },

        buildTagsList() {
            const list = [];
            const model = this.localModel;
            const formatDate = this.formatDateDisplay;

            if (model.sentTimestamp && (model.sentTimestamp.from || model.sentTimestamp.to)) {
                if (model.sentTimestamp.from) {
                    list.push(`${this.text('Data de envio')} - ${this.text('de')} ${formatDate(model.sentTimestamp.from)}`);
                }

                if (model.sentTimestamp.to) {
                    list.push(`${this.text('Data de envio')} - ${this.text('até')} ${formatDate(model.sentTimestamp.to)}`);
                }
            }

            const propToLabelKey = { categories: 'Categorias', proponentTypes: 'Tipos de proponente', ranges: 'Faixas/Linhas' };
            ['categories', 'proponentTypes', 'ranges'].forEach(prop => {
                const label = this.text(propToLabelKey[prop]);
                (model[prop] || []).forEach(value => list.push(`${label}: ${value}`));
            });

            if (model.distribution) {
                list.push(`${this.text('Distribuição')}: ${model.distribution}`);
            }

            if (model.fields && typeof model.fields === 'object') {
                Object.entries(model.fields).forEach(([fieldId, values]) => {
                    const field = this.selectionFields[fieldId];
                    const title = field?.title || fieldId;
                    (values || []).forEach(value => {
                        const displayValue = this.getFieldOptionLabel(fieldId, value);
                        list.push(`${title}: ${displayValue}`);
                    });
                });
            }

            return list.sort();
        },

        buildTagsLabels() {
            const labels = {};
            this.buildTagsList().forEach(tag => { labels[tag] = tag; });

            return labels;
        },

        removeTag(tag) {
            const model = { ...this.normalizeModel(this.localModel) };

            if (tag.includes(' - ')) {
                const parts = tag.split(' - ');
                const keyPart = parts[0].trim();
                const datePart = parts[1]?.trim() || '';

                if (keyPart === this.text('Data de envio') || keyPart.indexOf('envio') !== -1) {
                    const toLabel = (this.text('até') || 'até').toLowerCase();
                    const dateMatch = datePart.match(new RegExp(`^(de|${toLabel.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})\\s+(.+)$`, 'i'));
                    
                    if (dateMatch) {
                        const type = dateMatch[1].toLowerCase();
                        
                        if (type === 'de' || type === (this.text('de') || 'de').toLowerCase()) {
                            model.sentTimestamp.from = '';
                        } else {
                            model.sentTimestamp.to = '';
                        }

                        if (!model.sentTimestamp.from && !model.sentTimestamp.to) {
                            model.sentTimestamp = { from: '', to: '' };
                        }

                        this.localModel = this.normalizeModel(model);
                        this.$emit('update:modelValue', this.localModel);
                        return;
                    }
                }
            }

            const colonIndex = tag.indexOf(': ');
            
            if (colonIndex === -1) {
                return;
            }

            const displayKey = tag.substring(0, colonIndex);
            const value = tag.substring(colonIndex + 2);

            if (displayKey == this.text('Distribuição')) {
                model.distribution = '';
            }

            const isArrayKey = ['categories', 'proponentTypes', 'ranges'].includes(this.labelToKey(displayKey));
            if (isArrayKey) {
                const key = this.labelToKey(displayKey);
                
                if (Array.isArray(model[key])) {
                    model[key] = model[key].filter(item => item != value);
                }
            }

            if (model.fields && typeof model.fields == 'object') {
                const fieldId = this.labelToFieldId(displayKey);
                
                if (fieldId && Array.isArray(model.fields[fieldId])) {
                    const field = this.selectionFields[fieldId];
                    
                    if (field?.fieldType === 'checkbox') {
                        const checkedLabel = this.text('Marcado');
                        const uncheckedLabel = this.text('Desmarcado');
                        
                        let valueToRemove = null;
                        if (value === checkedLabel) {
                            valueToRemove = true;
                        } else if (value === uncheckedLabel) {
                            valueToRemove = false;
                        }
                        
                        if (valueToRemove !== null) {
                            model.fields[fieldId] = model.fields[fieldId].filter(item => item !== valueToRemove);
                        }
                    } else {
                        model.fields[fieldId] = model.fields[fieldId].filter(item => item != value);
                    }
                    
                    if (model.fields[fieldId].length == 0) {
                        delete model.fields[fieldId];
                    }
                }
            }

            this.localModel = this.normalizeModel(model);
            this.$emit('update:modelValue', this.localModel);
        },

        labelToKey(displayKey) {
            const map = { [this.text('Categorias')]: 'categories', [this.text('Tipos de proponente')]: 'proponentTypes', [this.text('Faixas/Linhas')]: 'ranges' };
            return map[displayKey] || null;
        },

        labelToFieldId(displayKey) {
            const entries = Object.entries(this.selectionFields || {});
            
            for (const [id, field] of entries) {
                if ((field.title || id) == displayKey) {
                    return id;
                }
            }

            return null;
        }
    }
});
