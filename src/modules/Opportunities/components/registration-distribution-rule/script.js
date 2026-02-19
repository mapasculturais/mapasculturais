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
            
            if (this.parentFilters && Array.isArray(this.parentFilters.categories) && this.parentFilters.categories.length > 0) {
                list = list.filter(cat => this.parentFilters.categories.includes(cat));
            }
 
            const disabled = this.disabledValues.categories || [];

            return list.filter(cat => !disabled.includes(cat));
        },

        filteredProponentTypes() {
            let list = this.registrationProponentTypes;
            
            if (this.parentFilters && Array.isArray(this.parentFilters.proponentTypes) && this.parentFilters.proponentTypes.length > 0) {
                list = list.filter(type => this.parentFilters.proponentTypes.includes(type));
            }

            const disabled = this.disabledValues.proponentTypes || [];
  
            return list.filter(type => !disabled.includes(type));
        },

        filteredRanges() {
            let list = this.registrationRanges;
            
            if (this.parentFilters && Array.isArray(this.parentFilters.ranges) && this.parentFilters.ranges.length > 0) {
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
                return { from: this.text('errorDateFromAfterTo'), to: '' };
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
            const v = val || {};

            return {
                categories: Array.isArray(v.categories) ? [...v.categories] : [],
                proponentTypes: Array.isArray(v.proponentTypes) ? [...v.proponentTypes] : [],
                ranges: Array.isArray(v.ranges) ? [...v.ranges] : [],
                distribution: typeof v.distribution == 'string' ? v.distribution : '',
                sentTimestamp: {
                    from: v.sentTimestamp?.from ?? '',
                    to: v.sentTimestamp?.to ?? ''
                },
                fields: v.fields && typeof v.fields == 'object' ? { ...v.fields } : {}
            };
        },

        applyParentRestrictions() {
            if (!this.parentFilters || !this.localModel) {
                return;
            }
            
            const next = { ...this.normalizeModel(this.localModel) };
            let changed = false;

            if (Array.isArray(this.parentFilters.categories) && this.parentFilters.categories.length > 0) {
                const allowed = new Set(this.parentFilters.categories);
                const prev = next.categories.length;
                next.categories = next.categories.filter(c => allowed.has(c));
                
                if (next.categories.length != prev) {
                    changed = true;
                }
            }

            if (Array.isArray(this.parentFilters.proponentTypes) && this.parentFilters.proponentTypes.length > 0) {
                const allowed = new Set(this.parentFilters.proponentTypes);
                const prev = next.proponentTypes.length;
                next.proponentTypes = next.proponentTypes.filter(p => allowed.has(p));
                
                if (next.proponentTypes.length != prev) {
                    changed = true;
                }
            }

            if (Array.isArray(this.parentFilters.ranges) && this.parentFilters.ranges.length > 0) {
                const allowed = new Set(this.parentFilters.ranges);
                const prev = next.ranges.length;
                next.ranges = next.ranges.filter(r => allowed.has(r));
                
                if (next.ranges.length != prev) {
                    changed = true;
                }
            }

            if (this.parentFilters.fields && typeof this.parentFilters.fields === 'object') {
                const nextFields = {};
                for (const [fieldId, allowedValues] of Object.entries(this.parentFilters.fields)) {
                    if (!Array.isArray(allowedValues) || allowedValues.length === 0) {
                        continue;
                    }

                    const set = new Set(allowedValues);
                    const current = next.fields[fieldId];
                    
                    if (Array.isArray(current)) {
                        const filtered = current.filter(v => set.has(v));
                        
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
                
                if (this.parentFilters && Array.isArray(this.parentFilters.categories)) {
                    return this.parentFilters.categories.length > 0;
                }

                return true;
            }

            if (type === 'proponentTypes') {
                if (this.registrationProponentTypes.length === 0) {
                    return false;
                }

                if (this.parentFilters && Array.isArray(this.parentFilters.proponentTypes)) {
                    return this.parentFilters.proponentTypes.length > 0;
                }

                return true;
            }

            if (type === 'ranges') {
                if (this.registrationRanges.length === 0) {
                    return false;
                }

                if (this.parentFilters && Array.isArray(this.parentFilters.ranges)) {
                    return this.parentFilters.ranges.length > 0;
                }

                return true;
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

                if (this.parentFilters && this.parentFilters.fields && typeof this.parentFilters.fields === 'object') {
                    return Object.keys(this.parentFilters.fields).some(id => Array.isArray(this.parentFilters.fields[id]) && this.parentFilters.fields[id].length > 0);
                }

                return true;
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
            if (!this.parentFilters?.fields || typeof this.parentFilters.fields !== 'object') {
                return true;
            }

            const allowed = this.parentFilters.fields[fieldId];
            return Array.isArray(allowed) && allowed.length > 0;
        },

        getFieldOptions(fieldId) {
            const field = this.selectionFields[fieldId];
            
            if (!field?.fieldOptions?.length) {
                return [];
            }

            const parentOpts = this.parentFilters?.fields?.[fieldId];
            
            if (Array.isArray(parentOpts) && parentOpts.length > 0) {
                return field.fieldOptions.filter(opt => parentOpts.includes(opt));
            }

            return field.fieldOptions;
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
                    list.push(`${this.text('sentTimestamp')} - ${this.text('de')} ${formatDate(model.sentTimestamp.from)}`);
                }

                if (model.sentTimestamp.to) {
                    list.push(`${this.text('sentTimestamp')} - ${this.text('toDate')} ${formatDate(model.sentTimestamp.to)}`);
                }
            }

            ['categories', 'proponentTypes', 'ranges'].forEach(prop => {
                const label = this.text(prop);
                (model[prop] || []).forEach(value => list.push(`${label}: ${value}`));
            });

            if (model.distribution) {
                list.push(`${this.text('distribution')}: ${model.distribution}`);
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

                if (keyPart === this.text('sentTimestamp') || keyPart.indexOf('envio') !== -1) {
                    const toLabel = (this.text('toDate') || 'até').toLowerCase();
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

            if (displayKey == this.text('distribution')) {
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
                    model.fields[fieldId] = model.fields[fieldId].filter(item => item != value);
                    
                    if (model.fields[fieldId].length == 0) {
                        delete model.fields[fieldId];
                    }
                }
            }

            this.localModel = this.normalizeModel(model);
            this.$emit('update:modelValue', this.localModel);
        },

        labelToKey(displayKey) {
            const map = { [this.text('categories')]: 'categories', [this.text('proponentTypes')]: 'proponentTypes', [this.text('ranges')]: 'ranges' };
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
