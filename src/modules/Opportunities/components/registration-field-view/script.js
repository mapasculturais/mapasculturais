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

    computed: {
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
            const files = fileConfigs.map((file) => ({
                ...file,
                fieldType: 'file',
                file: this.getFileForGroup(phase, file.groupName),
                step: file.step || null,
            }));

            const allFields = [...fieldConfigs, ...files].sort((a, b) => (a.displayOrder || 0) - (b.displayOrder || 0));
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
            const reg = this.phase;
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
