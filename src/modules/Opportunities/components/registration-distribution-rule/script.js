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
                            fieldOptions: field.fieldOptions || []
                        };
                    }
                });
            };

            const configs = opp?.registrationFieldConfigurations;

            if (configs && configs.length) {
                fromConfigs(configs);
            }

            if (Object.keys(byId).length === 0 && typeof $MAPAS !== 'undefined' && $MAPAS.config?.fetchSelectFields) {
                const selectFields = $MAPAS.config.fetchSelectFields;
                selectFields.forEach(field => {
                    if (!field) {
                        return;
                    }

                    const id = String(field.fieldName || field.id);
                    byId[id] = {
                        fieldName: field.fieldName || id,
                        title: field.title || field.fieldName || id,
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
            const disabled = this.disabledValues.categories || [];
            return this.registrationCategories.filter(cat => !disabled.includes(cat));
        },

        filteredProponentTypes() {
            const disabled = this.disabledValues.proponentTypes || [];
            return this.registrationProponentTypes.filter(type => !disabled.includes(type));
        },

        filteredRanges() {
            const disabled = this.disabledValues.ranges || [];
            return this.registrationRanges.filter(range => !disabled.includes(range));
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

        // Quando disableFilters vier como objeto, tratamos como valores desabilitados por tipo;
        // quando vier como array, continua sendo a lista de tipos desabilitados.
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
        }
    },

    mounted() {
        this.localModel = this.normalizeModel(this.modelValue);
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

        /** Verifica se o tipo de filtro está disponível (dados + não desabilitado) */
        isFilterAvailable(type) {
            if (this.disabledTypes.includes(type)) {
                return false;
            }
            if (type === 'categories') {
                if (this.registrationCategories.length === 0) {
                    return false;
                }
                return true;
            }
            if (type === 'proponentTypes') {
                if (this.registrationProponentTypes.length === 0) {
                    return false;
                }
                return true;
            }
            if (type === 'ranges') {
                if (this.registrationRanges.length === 0) {
                    return false;
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

        handleSelection() {
            const field = this.selectedField;
            const model = this.localModel;

            if (field === 'sentTimestamp') {
                this.selectedConfigs = {
                    from: model.sentTimestamp?.from ?? '',
                    to: model.sentTimestamp?.to ?? ''
                };
                return;
            }
            if (field === 'distribution') {
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
                    (values || []).forEach(value => list.push(`${title}: ${value}`));
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
