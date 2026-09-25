app.component('registration-field-view', {
    template: $TEMPLATES['registration-field-view'],

    props: {
        registration: {
            type: Entity,
            required: true,
        },
        phaseId: {
            type: Number,
            required: true,
        },
    },

    setup() {
        const text = Utils.getTexts('registration-field-view');
        return { text };
    },

    data() {
        return {
            selectedFieldId: null,
            documentaryStatusByFieldId: {},
        };
    },

    mounted() {
        window.addEventListener('message', this.handleDocumentaryMessage);
        this.hydrateDocumentaryStatuses();
    },

    beforeUnmount() {
        window.removeEventListener('message', this.handleDocumentaryMessage);
    },

    computed: {
        isDocumentaryEvaluation() {
            return this.registration?.opportunity?.evaluationMethod?.slug === 'documentary' ||
                !!$MAPAS?.config?.documentaryEvaluationForm;
        },

        isEvaluationContext() {
            return !!this.registration?.currentUserPermissions?.evaluate || !!$MAPAS?.viewUserEvaluation;
        },

        isOpportunityControl() {
            return !!this.registration?.opportunity?.currentUserPermissions?.['@control'];
        },

        phase() {
            const targetId = Number(this.phaseId);

            if (Number(this.registration.id) === targetId) {
                return this.registration;
            }

            const phasesMap = $MAPAS.registrationPhases || {};
            for (const key of Object.keys(phasesMap)) {
                const reg = phasesMap[key];
                if (reg && Number(reg.id) === targetId) {
                    return reg;
                }
            }

            let current = this.registration.nextPhase;
            while (current) {
                if (Number(current.id) === targetId) {
                    return current;
                }
                current = current.nextPhase;
            }

            return this.registration;
        },

        fields() {
            const phase = this.phase;
            if (!phase) {
                return [];
            }

            const oppId = phase.opportunity?.id;
            const byOpp = $MAPAS.registrationFieldsByOpportunity || {};
            const serializedForOpp =
                oppId != null ? (byOpp[oppId] ?? byOpp[String(oppId)]) : null;

            const serializedFields = $MAPAS.registrationFields || [];
            const formConfig = $MAPAS.config?.registrationForm || {};
            const splitSerialized = serializedFields.length
                ? {
                    fields: serializedFields.filter((field) => !!field.fieldName),
                    files: serializedFields.filter((field) => !!field.groupName),
                }
                : null;

            const splitForPhase =
                serializedForOpp && serializedForOpp.length
                    ? {
                        fields: serializedForOpp.filter((field) => !!field.fieldName),
                        files: serializedForOpp.filter((field) => !!field.groupName),
                    }
                    : null;

            const fieldConfigs =
                splitForPhase?.fields ||
                splitSerialized?.fields ||
                formConfig.fields ||
                phase.opportunity?.registrationFieldConfigurations ||
                [];
            const fileConfigs =
                splitForPhase?.files ||
                splitSerialized?.files ||
                formConfig.files ||
                phase.opportunity?.registrationFileConfigurations ||
                [];
            const normalizedFieldConfigs = (fieldConfigs || []).filter((field) => !!field);
            const normalizedFileConfigs = (fileConfigs || []).filter((file) => !!file);

            const files = normalizedFileConfigs.map((file) => ({
                ...file,
                fieldType: 'file',
                file: this.getFileForGroup(phase, file.groupName),
                step: file.step || null,
            }));

            const allFields = [...normalizedFieldConfigs, ...files]
                .filter((field) => !!field)
                .sort((a, b) => (a.displayOrder || 0) - (b.displayOrder || 0));
            return allFields.filter((field) => this.showField(field));
        },

        fieldsByStep() {
            return this.fields.reduce((acc, field) => {
                const stepName = field.step?.name || '';
                if (!acc[stepName]) {
                    acc[stepName] = [];
                }
                acc[stepName].push(field);
                return acc;
            }, {});
        },

        stepTabs() {
            const entries = Object.entries(this.fieldsByStep);
            return entries.map(([stepName, fields], index) => {
                const label = stepName || this.text('Etapa');
                const slugBase = stepName || `etapa-${index + 1}`;
                const slug = String(slugBase)
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '') || `etapa-${index + 1}`;

                return {
                    label,
                    slug,
                    fields,
                };
            });
        },
    },

    methods: {
        isTruthyEvaluationField(value) {
            return value === true || value === 'true' || value === 1 || value === '1';
        },

        getEvaluationVisibilityMaps(registrationPhase) {
            const currentOpportunityMap = this.registration?.opportunity?.avaliableEvaluationFields;
            const phaseMap = registrationPhase?.opportunity?.avaliableEvaluationFields;
            const globalMap = $MAPAS?.avaliableEvaluationFields;
            const normalizedCurrent = (currentOpportunityMap && typeof currentOpportunityMap === 'object') ? currentOpportunityMap : {};
            const normalizedGlobal = (globalMap && typeof globalMap === 'object') ? globalMap : {};
            const normalizedPhase = (phaseMap && typeof phaseMap === 'object') ? phaseMap : {};

            return {
                phase: normalizedPhase,
                current: normalizedCurrent,
                global: normalizedGlobal,
            };
        },

        hasOwnKey(map, key) {
            return Object.prototype.hasOwnProperty.call(map || {}, key);
        },

        resolveVisibilityByKeys(keys, registrationPhase) {
            const maps = this.getEvaluationVisibilityMaps(registrationPhase);
            const orderedMaps = [maps.phase, maps.current, maps.global];

            for (const map of orderedMaps) {
                const existingKeys = keys.filter((key) => this.hasOwnKey(map, key));
                if (!existingKeys.length) {
                    continue;
                }

                return existingKeys.some((key) => this.isTruthyEvaluationField(map[key]));
            }

            return false;
        },

        hydrateDocumentaryStatuses() {
            if (!this.isDocumentaryEvaluation) {
                return;
            }

            const persisted =
                $MAPAS?.config?.documentaryEvaluationForm?.evaluationData?.evaluationData || {};

            const statusByFieldId = {};
            this.fields.forEach((field) => {
                const fieldId = this.getFieldId(field);
                if (fieldId == null) {
                    return;
                }

                const saved = persisted[String(fieldId)] || persisted[fieldId];
                const status = saved?.evaluation;
                if (status === 'valid' || status === 'invalid' || status === 'empty') {
                    statusByFieldId[String(fieldId)] = status;
                }
            });

            this.documentaryStatusByFieldId = statusByFieldId;
        },

        getFieldId(field) {
            return field?.id ?? field?._id ?? null;
        },

        getFieldType(field) {
            if (field?.fieldType) {
                return field.fieldType;
            }
            if (field?.groupName) {
                return 'file';
            }
            return 'field';
        },

        getFieldVisibilityKey(field) {
            if (this.getFieldType(field) === 'file') {
                return field?.groupName || field?.ref || null;
            }

            return field?.fieldName || field?.ref || null;
        },

        getFieldVisibilityKeys(field) {
            const keys = [];
            const id = this.getFieldId(field);

            if (field?.fieldName) {
                keys.push(field.fieldName);
            }
            if (field?.groupName) {
                keys.push(field.groupName);
            }
            if (field?.ref) {
                keys.push(field.ref);
            }
            if (id != null) {
                keys.push(`field_${id}`);
            }

            return keys
                .filter(Boolean)
                .filter((key, index, arr) => arr.indexOf(key) === index);
        },

        fieldDomId(field) {
            const fieldId = this.getFieldId(field);
            return fieldId != null ? `field_${fieldId}` : null;
        },

        fieldClasses(field) {
            const fieldId = String(this.getFieldId(field) ?? '');
            
            return [
                'attachment-list-item',
                'registration-view-mode',
                'registration-field-view__item',
                this.isDocumentaryEvaluation && this.getFieldType(field) !== 'section' ? 'js-field' : null,
                this.isDocumentaryEvaluation && this.getFieldType(field) !== 'section' ? 'registration-field-view__item--clickable' : null,
                this.selectedFieldId === fieldId ? 'field-shadow' : null,
                this.documentaryStatusByFieldId[fieldId] ? `evaluation-${this.documentaryStatusByFieldId[fieldId]}` : null,
            ];
        },

        handleFieldClick(field) {
            const fieldIdRaw = this.getFieldId(field);
            if (!this.isDocumentaryEvaluation || this.getFieldType(field) === 'section' || fieldIdRaw == null) {
                return;
            }

            const fieldId = String(fieldIdRaw);
            const wasSelected = this.selectedFieldId === fieldId;
            this.selectedFieldId = wasSelected ? null : fieldId;

            window.dispatchEvent(new CustomEvent('documentaryData', {
                detail: {
                    type: wasSelected ? 'evaluationForm.closeForm' : 'evaluationForm.openForm',
                    fieldName: this.fieldDomId(field),
                    fieldId: fieldId,
                    fieldType: this.getFieldType(field),
                    fieldLabel: String(field?.title || '').trim(),
                },
            }));
        },

        handleDocumentaryMessage(event) {
            if (!this.isDocumentaryEvaluation) {
                return;
            }

            switch (event?.data?.type) {
                case 'evaluationRegistration.setClass': {
                    const status = String(event.data.className || '').replace('evaluation-', '');
                    const fieldId = String(event.data.fieldId || this.selectedFieldId || '');
                    if (!fieldId || !status) {
                        return;
                    }
                    this.documentaryStatusByFieldId[fieldId] = status;
                    break;
                }
                case 'evaluationRegistration.clearStyles':
                    this.selectedFieldId = null;
                    break;
            }
        },

        getFileForGroup(phase, groupName) {
            const phaseFile = phase?.files?.[groupName];
            if (phaseFile) {
                return Array.isArray(phaseFile) ? phaseFile[0] : phaseFile;
            }

            const legacyFile = phase?.registrationFiles?.[groupName];
            if (legacyFile) {
                return Array.isArray(legacyFile) ? legacyFile[0] : legacyFile;
            }

            return null;
        },

        parseValue(value) {
            if (typeof value !== 'string') {
                return value;
            }

            const trimmed = value.trim();
            if (!trimmed) {
                return value;
            }

            if ((trimmed.startsWith('{') && trimmed.endsWith('}')) || (trimmed.startsWith('[') && trimmed.endsWith(']'))) {
                try {
                    return JSON.parse(trimmed);
                } catch (e) {
                    return value;
                }
            }

            return value;
        },

        hasValue(value) {
            const parsed = this.parseValue(value);
            if (parsed === null || parsed === undefined || parsed === '') {
                return false;
            }
            if (Array.isArray(parsed)) {
                return parsed.length > 0;
            }
            if (typeof parsed === 'object') {
                return Object.keys(parsed).length > 0;
            }
            return true;
        },

        showField(field) {
            if (!field) {
                return false;
            }

            const reg = this.phase;
            if (!reg) {
                return false;
            }
            if (field.categories?.length && !field.categories.includes(reg.category)) {
                return false;
            }
            if (field.registrationRanges?.length && !field.registrationRanges.includes(reg.range)) {
                return false;
            }
            if (field.proponentTypes?.length && !field.proponentTypes.includes(reg.proponentType)) {
                return false;
            }
            if (field.conditional) {
                const fieldName = field.conditionalField;
                const fieldValue = field.conditionalValue;
                const currentValue = reg[fieldName];

                if (Array.isArray(currentValue)) {
                    return currentValue.includes(fieldValue);
                }
                if (fieldName === 'appliedForQuota') {
                    return currentValue === true || currentValue === 'true';
                }
                return currentValue == fieldValue;
            }

            if (this.isEvaluationContext && !this.isOpportunityControl) {
                const fieldKeys = this.getFieldVisibilityKeys(field);
                if (fieldKeys.length) {
                    const isVisible = this.resolveVisibilityByKeys(fieldKeys, reg);
                    if (!isVisible) {
                        return false;
                    }
                }
            }

            return true;
        },

        isLinkField(field) {
            return field.config?.entityField === '@links' ||
                field.fieldType === 'links' ||
                $DESCRIPTIONS.registration[field.fieldName]?.field_type === 'links';
        },

        isLocationField(field) {
            return field.config?.entityField === '@location';
        },

        bankData(value) {
            const data = this.parseValue(value) || {};
            const dict = $MAPAS.bank_data_dict || {};
            return {
                account_type: dict.account_types?.[data.account_type] || data.account_type || '',
                number: dict.bank_types?.[data.number] || data.number || '',
                branch: data.branch || '',
                dv_branch: data.dv_branch || '',
                account_number: data.account_number || '',
                dv_account_number: data.dv_account_number || '',
            };
        },

        formatArrayLike(value) {
            if (!value) {
                return '';
            }
            const parsed = this.parseValue(value);
            if (Array.isArray(parsed)) {
                return parsed.join(', ');
            }
            if (typeof parsed === 'object') {
                return Object.keys(parsed).filter((key) => parsed[key]).join(', ');
            }
            return parsed;
        },

        locationEntries(value) {
            const location = this.parseValue(value) || {};
            return Object.entries(location).filter(([key, item]) => {
                return key !== 'location' && key !== 'publicLocation' && item && !key.startsWith('field');
            });
        },

        getAddressLabel(key, country) {
            const normalizedKey = String(key || '').toLowerCase();
            if (normalizedKey === 'endereco') {
                return this.text('Endereço completo');
            }

            const conf = $MAPAS.config?.countryLocalization;
            const byCountry = conf?.labelsByCountry;
            if (byCountry?.[country]?.[key]) {
                return byCountry[country][key];
            }
            if (byCountry?.BR?.[key]) {
                return byCountry.BR[key];
            }
            return key;
        },

        personValue(person, key) {
            return this.formatArrayLike(person?.[key]);
        },
    },
});
