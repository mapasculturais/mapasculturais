app.component('documentary-evaluation-form', {
    template: $TEMPLATES['documentary-evaluation-form'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: true
        },

        formData: { 
            type: Object,
            required: true
        }
    },

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('documentary-evaluation-form');

        return { text, messages };
    },

    created() {
        this.formData.data = {};
    },

    mounted() {
        window.addEventListener('evaluationRegistrationList', this.getEvaluationList);
        window.addEventListener('documentaryData', this.getDocumentaryData);
        window.addEventListener('responseEvaluation', this.processResponse);
        window.addEventListener('processErrors', this.validateErrors);

        this.isEditable = this.canEvaluate();
    },

    data() {
        return {
            enableForm: false,
            fieldName: '',
            fieldId: null,
            fieldType: null,
            userId: null,
            userName: '',
            isEditable: this.editable,
            evaluationData: $MAPAS.config.documentaryEvaluationForm.evaluationData?.evaluationData || {},
            newStatus: null,
            lockedFieldSeals: this.entity.__lockedFieldSeals,
        };
    },

    computed: {
        status() {
            return $MAPAS.config.documentaryEvaluationForm.evaluationData?.status || 0;
        },

        needsTiebreaker() {
            return $MAPAS.config.documentaryEvaluationForm.needsTieBreaker;
        },

        isMinervaGroup() {
            return $MAPAS.config.documentaryEvaluationForm.isMinervaGroup;
        },

        enableExternalReviews() {
            return $MAPAS.config.documentaryEvaluationForm.showExternalReviews;
        },

        evaluationName() {
            return $MAPAS.config.documentaryEvaluationForm.evaluationMethodName;
        },

        currentFieldInfo() {
            return $MAPAS.config.documentaryEvaluationForm.fieldsInfo[this.fieldName] || {};
        },

        currentSealValidators() {
            return this.currentFieldInfo.sealValidators || [];
        }
    },

    methods: {
        getDocumentaryData(data) {
            this.fieldType = data.detail.fieldType;
            this.fieldName = this.fieldType === 'file' ? data.detail.fieldName.replace('field_', 'rfc_') : data.detail.fieldName;
            this.enableForm = data.detail.type === 'evaluationForm.openForm';
            this.fieldId = data.detail.fieldId;
            
            if (this.enableForm) {
                this.getEvaluationData();

                this.formData.uid = this.userId;
                this.formData.data[this.fieldId] = {
                    label: this.currentFieldInfo?.label || '',
                    obsItems: this.evaluationData[this.fieldId]?.obsItems ?? '',
                    obs: this.evaluationData[this.fieldId]?.obs ?? '',
                    evaluation: this.evaluationData[this.fieldId]?.evaluation ?? '',
                };

                this.markSealValidatorField();
            }
        },

        validateErrors() {
            let hasError = false;
            const global = useGlobalState();

            global.validateEvaluationErrors = hasError;

            return hasError;
        },

        getEvaluationList(data) {
            let evaluationRegistrationList = data.detail.evaluationRegistrationList ?? null;
            if (evaluationRegistrationList) {
                evaluationRegistrationList.forEach(item => {
                    if (item.valuer && item.valuer.user === $MAPAS.userId) {
                        this.userId = item.valuer.user;
                        this.userName = item.valuer.name;
                    }
                });
            }
        },

        processResponse(data) {
            this.newStatus = data.detail.response.status;
            this.isEditable = this.newStatus >= 1 ? false : true;
        },

        setEvaluationData(fieldId, status = null) {
            this.evaluationData[fieldId] = this.formData.data[fieldId];

            if(status) {
                let className = `evaluation-${status}`;

                window.parent.postMessage({
                    type: 'evaluationRegistration.setClass',
                    className: className,
                    fieldId: fieldId
                });
            }
        },

        markSealValidatorField() {
            const seals = this.getSealInfo(this.fieldId);

            this.postToEvaluationRegistration({
                type: 'evaluationRegistration.setSealValidator',
                fieldId: this.fieldId,
                isValidator: seals.length > 0 || this.currentSealValidators.length > 0,
                hasInvalidator: seals.some((seal) => seal.isInvalidator) || this.currentSealValidators.some((validator) => validator.isInvalidator),
                seals,
            });
        },

        postToEvaluationRegistration(message) {
            window.parent.postMessage(message, '*');
            document.getElementById('evaluation-registration')?.contentWindow?.postMessage(message, '*');
        },

        getEvaluationData() {
            const data = $MAPAS.config?.documentaryEvaluationForm?.evaluationData?.evaluationData;
            
            if((data && this.fieldId && !this.isEditable) || (data && Object.values(this.evaluationData).length == 0)) {
                return this.evaluationData[this.fieldId] = data[this.fieldId];
            }

            return {};
        },

        canEvaluate() {
            if(!this.entity.currentUserPermissions['evaluate']) {
                return false;
            }
    
            if(this.status >= 1) {
                return false;
            }
    
            return true;
        },

        hasSealInfo(fieldId) {
            return this.getSealInfo(fieldId).length > 0;
        },

        getSealInfo(fieldId) {
            const fieldName = `field_${fieldId}`;
            const fieldSealStatuses = this.entity.$fieldSealStatuses?.[fieldName] || [];
            if (fieldSealStatuses.length > 0) {
                return fieldSealStatuses.map((seal) => this.normalizeSealInfo(seal));
            }

            const lockedFieldSeals = this.entity.$lockedFieldSeals?.[fieldName] || [];
            if (lockedFieldSeals.length > 0) {
                return lockedFieldSeals.map((seal) => this.normalizeSealInfo(seal));
            }

            const validatorSeals = this.getValidatorSealInfo(fieldId);
            if (validatorSeals.length > 0) {
                return validatorSeals.map((seal) => this.normalizeSealInfo(seal));
            }

            return [];
        },

        getValidatorSealInfo(fieldId) {
            const validatorsConfig = $MAPAS.config?.evaluationFormSealValidators?.fields || [];
            const fieldConfig = validatorsConfig.find((field) => String(field.fieldId) === String(fieldId));

            if (!fieldConfig) {
                return this.currentSealValidators.map((validator) => this.normalizeValidatorSeal(validator));
            }

            return (fieldConfig.validators || []).map((validator) => this.normalizeValidatorSeal(validator));
        },

        normalizeValidatorSeal(validator) {
            const entitySeal = this.getEntitySealById(validator.sealId);

            return {
                ...entitySeal,
                sealId: validator.sealId,
                sealName: validator.sealName,
                fieldName: validator.fieldName,
                fieldStatus: validator.fieldStatus || entitySeal.fieldStatus,
                expiryDate: validator.expiryDate || entitySeal.expiryDate,
                isInvalidator: validator.isInvalidator ?? entitySeal.isInvalidator,
                isUnlocked: validator.isUnlocked ?? entitySeal.isUnlocked,
                isLocked: validator.isLocked ?? entitySeal.isLocked,
                hasSealRelation: validator.hasSealRelation ?? entitySeal.hasSealRelation,
                validateDate: validator.validateDate || entitySeal.validateDate,
                createTimestamp: validator.createTimestamp || entitySeal.createTimestamp,
                files: validator.files || entitySeal.files,
            };
        },

        getEntitySealById(sealId) {
            const seals = Array.isArray(this.entity.seals) ? this.entity.seals : Object.values(this.entity.seals || {});
            return seals.find((seal) => String(seal.sealId || seal.id) === String(sealId)) || {};
        },

        normalizeSealInfo(seal) {
            const fieldStatus = seal.fieldStatus || 'no_expiration';
            const validateDate = seal.validateDate || (seal.createTimestamp?.date ? new McDate(seal.createTimestamp.date).date('2-digit year') : '');

            return {
                sealId: seal.sealId || seal.id,
                sealRelationId: seal.sealRelationId,
                fieldName: seal.fieldName,
                name: seal.name || seal.sealName,
                fieldStatus,
                expiryDate: seal.expiryDate,
                isInvalidator: Boolean(seal.isInvalidator),
                hasSealRelation: seal.hasSealRelation ?? Boolean(validateDate),
                validateDate,
                expiryDateLabel: this.sealExpiryLabel(seal),
                files: {
                    avatar: seal.files?.avatar
                }
            };
        },

        sealExpiryLabel(seal) {
            if (!seal.expiryDate) {
                return '';
            }

            if (seal.fieldStatus === 'expired') {
                return `expirou em ${seal.expiryDate}`;
            }

            if (seal.fieldStatus === 'about_to_expire') {
                return `expira em ${seal.expiryDate}`;
            }

            return `válido até ${seal.expiryDate}`;
        },

        sealStatusLabel(seals) {
            if (!seals.some((seal) => seal.hasSealRelation || seal.validateDate)) {
                return 'Sem validação registrada';
            }

            if (seals.some((seal) => seal.fieldStatus === 'expired')) {
                return 'Expirado';
            }

            if (seals.some((seal) => seal.fieldStatus === 'about_to_expire')) {
                return 'Prestes a expirar';
            }

            return 'Validado';
        },

        sealAlertClass(seals) {
            if (!seals.some((seal) => seal.hasSealRelation || seal.validateDate)) {
                return 'documentary-evaluation-form__seal-alert--invalid';
            }

            if (seals.some((seal) => seal.fieldStatus === 'expired')) {
                return 'documentary-evaluation-form__seal-alert--invalid';
            }

            if (seals.some((seal) => seal.fieldStatus === 'about_to_expire')) {
                return 'documentary-evaluation-form__seal-alert--about-to-expire';
            }

            return 'documentary-evaluation-form__seal-alert--valid';
        },

        sealAlertTitle(seals) {
            if (!seals.some((seal) => seal.hasSealRelation || seal.validateDate)) {
                return 'Campo sem validação por selo';
            }

            if (seals.some((seal) => seal.fieldStatus === 'expired')) {
                return 'Validação por selo expirada';
            }

            if (seals.some((seal) => seal.fieldStatus === 'about_to_expire')) {
                return 'Validação por selo prestes a vencer';
            }

            return 'Campo validado por selo';
        },

        sealAlertMessage(seals) {
            const sealsInfo = this.formatSealsInfo(seals);
            const hasValidationInfo = seals.some((seal) => seal.hasSealRelation || seal.validateDate);
            const evaluatorInstruction = seals.some((seal) => seal.isInvalidator)
                ? 'Se o dado informado não conferir, marque este campo como inválido.'
                : 'Confira o dado antes de concluir a avaliação.';

            if (!hasValidationInfo) {
                return `Este campo exige validação por ${sealsInfo}, mas não há registro de selo válido para o proponente desta inscrição. ${evaluatorInstruction}`;
            }

            if (seals.some((seal) => seal.fieldStatus === 'expired')) {
                return `Este campo teve validação por ${sealsInfo}, mas a validade expirou. ${this.formatSealStatusInfo(seals)} ${evaluatorInstruction}`;
            }

            if (seals.some((seal) => seal.fieldStatus === 'about_to_expire')) {
                return `Este campo foi validado por ${sealsInfo}, mas a validação está prestes a vencer. ${this.formatSealStatusInfo(seals)} ${evaluatorInstruction}`;
            }

            if (seals.some((seal) => seal.fieldStatus === 'no_expiration')) {
                return `Este campo foi validado por ${sealsInfo} e não possui prazo de expiração configurado. ${evaluatorInstruction}`;
            }

            return `Este campo foi validado por ${sealsInfo}. ${this.formatSealStatusInfo(seals)} ${evaluatorInstruction}`;
        },

        formatSealStatusInfo(seals) {
            const statusMessages = seals
                .map((seal) => {
                    if (seal.fieldStatus === 'expired' && seal.expiryDate) {
                        return `A validação deste campo expirou em ${this.escapeHtml(seal.expiryDate)}.`;
                    }

                    if (seal.fieldStatus === 'about_to_expire' && seal.expiryDate) {
                        return `A validação deste campo expira em ${this.escapeHtml(seal.expiryDate)}.`;
                    }

                    if (seal.expiryDate) {
                        return `A validação deste campo é válida até ${this.escapeHtml(seal.expiryDate)}.`;
                    }

                    if (seal.hasSealRelation || seal.validateDate) {
                        return 'Esta validação não possui data de expiração configurada.';
                    }

                    return '';
                })
                .filter((message) => message !== '');

            return statusMessages.join(' ');
        },

        formatSealsInfo(seals) {
            return seals.map((seal) => {
                const name = this.escapeHtml(seal.name || 'selo');
                const date = seal.validateDate ? ` em ${this.escapeHtml(seal.validateDate)}` : '';
                return `<strong>${name}</strong>${date}`;
            }).join(', ');
        },

        escapeHtml(value) {
            const element = document.createElement('div');
            element.innerText = value;
            return element.innerHTML;
        },

        validatorStatusLabel() {
            const status = this.formData.data[this.fieldId]?.evaluation;
            if (status === 'valid') {
                return this.text('campoValidado') || 'Campo validado';
            }
            if (status === 'invalid') {
                return this.text('campoInvalidado') || 'Campo invalidado';
            }
            return this.text('campoPendente') || 'Validação pendente';
        },
    },
});
