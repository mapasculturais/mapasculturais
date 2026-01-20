app.component('entity-field', {
    template: $TEMPLATES['entity-field'],
    emits: ['change', 'save'],

    setup(props) {
        const propId = Vue.useId();
        return { propId };
    },

    data() {         
        let description, 
            value = this.entity[this.prop];

        description = this.entity.$PROPERTIES[this.prop] || {};
        
        if (description.type == 'array' && !(value instanceof Array)) {
            if (!value) {
                value = [];
            } else {
                value = [value];
            }
        }
        
        // Garantir que custom-table é sempre um array
        if (description.registrationFieldConfiguration?.fieldType === 'custom-table') {
            if (typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                } catch(e) {
                    value = [];
                }
            }
            if (!Array.isArray(value)) {
                value = [];
            }
            this.entity[this.prop] = value;
        }
        
        let isAdmin = function() {
            let result = false;
            $MAPAS.currentUserRoles.forEach(function(item){
                if(item.toLowerCase().match('admin')){
                    result = true;
                    return;
                }
            })

            return result;
        }

        if(this.entity.__objectType === "agent" && this.prop === "type" && !isAdmin()){
            
            var typeOptions = {};
            var optionsOrder = [];
            Object.keys(description.options).forEach(function(item, index){
                if(item != 1){
                    typeOptions[index] = description.options[item];
                    optionsOrder.push(parseInt(index));
                }
            });
            description.options = typeOptions;
            description.optionsOrder = optionsOrder;
        }

        let fieldType = this.type || description.field_type || description.type;

        if(this.type == 'textarea' || (description.type == 'text' && description.field_type === undefined)) {
            fieldType = 'textarea';
        }

        // Tratamento especial para campos de galeria/vídeos/downloads
        if(description.registrationFieldConfiguration?.config?.entityField) {
            const entityField = description.registrationFieldConfiguration.config.entityField;
            if(entityField === '@gallery') {
                fieldType = 'gallery';
            } else if(entityField === '@videos') {
                fieldType = 'videos';
            } else if(entityField === '@downloads') {
                fieldType = 'downloads';
            }
        }

        if (!description.min) {
            description.min = 0;
        }

        /**
         * Aqui podemos passar alguns itens que eventualmente não queremos que sejam listados em alguma tela
         */
        if (this.entity.removeOptions && description.options) {
            const removedOptions = [];
            const { removeOptions } = this.entity;
        
            description.options = Object.fromEntries(
                Object.entries(description.options).filter(([key, value]) => {
                    const optionFound = removeOptions.includes(value);
                    if (optionFound) {
                        removedOptions.push(parseInt(key));
                    }

                    return !optionFound;
                })
            );
        
            description.optionsOrder = Array.isArray(description.optionsOrder)
                ? description.optionsOrder.filter(key => !removedOptions.includes(key))
                : Object.values(description.optionsOrder).filter(item => !removedOptions.includes(item));
        }

        return {
            __timeout: null,
            description: description,
            fieldType,
            currencyValue: this.entity[this.prop],
            readonly: false,
            selectedOptions: [],
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        prop: {
            type: String,
            required: true
        },
        label: {
            type: String,
            default: null
        },
        placeholder: {
            type: String
        },
        type: {
            type: String,
            default: null
        },
        hideLabel: {
            type: Boolean,
            default: false
        },
        hideDescription: {
            type: Boolean,
            default: false
        },
        hideRequired: {
            type: Boolean,
            default: false
        },
        debounce: {
            type: Number,
            default: 0
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        min: {
            type: [ Number, String, Date ],
            default: null
        },
        max: {
            type: [ Number, String, Date ],
            default: null
        },

        maxLength: {
            type: Number,
            default: null
        },

        fieldDescription: {
            type: String,
            default: null
        },
        autosave: {
            type: Number,
        },
        disabled: {
            type: Boolean,
            default: false
        },
        mask: {
            type: String,
            default: null,
        },

        maxOptions: {
            type: Number,
            default: 0
        },

        descriptionFirst: {
            type: Boolean,
            default: false
        },

        editable: {
            type: Boolean,
            default: false
        },

        preserveOrder: {
            type: Boolean,
            default: false
        },
        titleModal: {
            type: String,
            required: false,
            default: 'Anexar'
        },
        groupName: {
            type: String,
            required: false,
        }
    },

    created() {
        this.isReadonly();

        window.addEventListener(
            "entitySave",
            this.isReadonly
        );

        if (this.isMultiSelect()) {
            if (!this.entity[this.prop]) {
                this.entity[this.prop] = [];
            } else if (typeof this.entity[this.prop] !== 'object') {
                this.entity[this.prop] = this.entity[this.prop].split(';');
            }
            
            this.selectedOptions[this.prop] = [...this.entity[this.prop]];
        }

        // Inicializar dados da tabela customizável
        if (this.is('custom-table')) {
            if (!this.entity[this.prop]) {
                this.entity[this.prop] = [];
            } else if (!Array.isArray(this.entity[this.prop])) {
                // Se não for array, tentar fazer parse
                if (typeof this.entity[this.prop] === 'string') {
                    try {
                        this.entity[this.prop] = JSON.parse(this.entity[this.prop]);
                    } catch(e) {
                        this.entity[this.prop] = [];
                    }
                } else {
                    this.entity[this.prop] = [];
                }
            }
            
            // Remover linhas que não são objetos válidos (arrays vazios, null, etc)
            this.entity[this.prop] = this.entity[this.prop].filter(row => {
                return row && typeof row === 'object' && !Array.isArray(row);
            });
            
            // Adicionar linhas mínimas se necessário
            const minRows = this.description.registrationFieldConfiguration?.config?.minRows || 0;
            while (this.entity[this.prop].length < minRows) {
                this.entity[this.prop].push({});
            }
        }
    },

    mounted() {
        if(this.is('textarea')) {
            this.$refs.textarea.style.height = "auto";
            this.$refs.textarea.style.height = (this.$refs.textarea.scrollHeight +10) + "px";
        }
    },

    computed: {
        hasErrors() {
            let errors = this.entity.__validationErrors[this.prop] || [];
            if(errors.length > 0){
                return true;
            } else {
                return false;
            }
        },
        errors() {
            return this.entity.__validationErrors[this.prop];
        },
        value() {
            return this.entity[this.prop]?.id ?? this.entity[this.prop];
        },
        tableData() {
            if (this.is('custom-table')) {
                return this.entity[this.prop] || [];
            }
            return [];
        },
        entitiesFildTypes() {
            return ['agent-owner-field', 'agent-collective-field']
        },
        fileGroupTypes() {
            return ['@gallery', '@downloads']
        },
        metaListTypes() {
            return ['@videos', '@links']
        },
        isFileGroup() {
            let registrationFieldConfiguration = this.description.registrationFieldConfiguration;
            if(registrationFieldConfiguration?.config?.entityField) {
                return this.fileGroupTypes().includes(registrationFieldConfiguration.config.entityField);
            }
            return false;
        },
        isMetaList() {
            let registrationFieldConfiguration = this.description.registrationFieldConfiguration;
            if(registrationFieldConfiguration?.config?.entityField) {
                return this.metaListTypes().includes(registrationFieldConfiguration.config.entityField);
            }
            return false;
        },
    },
    
    methods: {
        isRadioChecked(value, optionValue) {
            if(value == optionValue) {
                return true;
            }

            if(value == null && this.description?.default) {
                return optionValue == this.description?.default;
            }
            
            return false;            
        },
        propExists(){
            return !! this.entity.$PROPERTIES[this.prop];
        },

        change(event, now) {
            clearTimeout(this.__timeout);
            let oldValue = this.entity[this.prop] ? JSON.parse(JSON.stringify(this.entity[this.prop])) : null;
            
            this.__timeout = setTimeout(() => {
               if(this.is('date') || this.is('datetime') || this.is('time')) {
                    if(event) {
                        this.entity[this.prop] = new McDate(event);
                    } else {
                        this.entity[this.prop] = '';
                    }

                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event});
                } else if(this.is('currency')) {
                    this.entity[this.prop] = this.currencyValue;
                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event.target.checked});
                } else if(this.is('checkbox')) {
                    this.entity[this.prop] = event.target.checked;
                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event.target.checked});
                } else if (this.is("bankFields")) {
                    let fieldEmpty = false;

                    if(this.description.required){
                        Object.keys(event).forEach(field => {
                            if(!event[field]){
                                fieldEmpty = true;
                            }
                        });
                    }

                    if(!fieldEmpty){
                        this.entity.__validationErrors = {};
                        this.entity[this.prop] = event;

                        this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event});
                    }else {
                        this.entity.__validationErrors = {
                            ...this.entity.__validationErrors,
                            [this.prop]:  ['Os dados bancarios são obrigatorios'],
                        }
                    }
                    
                } else if (this.is('multiselect') || this.is('checklist')) {
                    if (this.entity[this.prop] === '' || !this.entity[this.prop]) {
                        this.entity[this.prop] = []
                    } else if (typeof this.entity[this.prop] !== 'object') {
                        this.entity[this.prop] = this.entity[this.prop].split(";");
                    }

                    let value = event.target ? event.target.value : event; 
                    let index = this.entity[this.prop].indexOf(value);

                    if(value == "@NA") {
                        if (index < 0) {
                            this.entity[this.prop] = ["@NA"];
                        } else {
                            this.entity[this.prop].splice(index, 1);
                        }
                    } else {
                        const ndIndex = this.entity[this.prop].indexOf("@NA");

                        if (ndIndex >= 0) {
                            this.entity[this.prop].splice(ndIndex, 1);
                        }

                        if (index >= 0) {
                            this.entity[this.prop].splice(index, 1);
                        } else {
                            if(!this.isMultiSelect() && !this.maxOptions || this.entity[this.prop].length < this.maxOptions) {
                                this.entity[this.prop].push(value)
                            } else {
                                this.entity[this.prop].push(value)
                            }
                        }
                    }

                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: value});
                } else if(this.is('links')) { 
                    this.entity[this.prop] = event; 

                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event});
                } else if(this.is('municipio')) {
                    this.entity[this.prop] = event; 

                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event});
                } else {
                    this.entity[this.prop] = event.target.value;
                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event.target.value});
                }

                if (this.autosave && (now || JSON.stringify(this.entity[this.prop]) != JSON.stringify(oldValue))) {
                    this.entity.save(this.autosave).then(() => {
                        this.$emit('save', this.entity);
                    });
                }

            }, this.debounce);


            if(this.is('textarea') && event.target) {
                event.target.style.height = "auto";
                event.target.style.height = (event.target.scrollHeight + 20) + "px";
            }
        },

        is(type) {
            if (type == 'location') {
                let fieldConfig = this.description.registrationFieldConfiguration?.config;
                return fieldConfig?.entityField == '@location';
            }
            if (type == 'gallery') {
                let fieldConfig = this.description.registrationFieldConfiguration?.config;
                return fieldConfig?.entityField == '@gallery';
            }
            if (type == 'videos') {
                let fieldConfig = this.description.registrationFieldConfiguration?.config;
                return fieldConfig?.entityField == '@videos';
            }
            if (type == 'downloads') {
                let fieldConfig = this.description.registrationFieldConfiguration?.config;
                return fieldConfig?.entityField == '@downloads';
            }
            return this.fieldType == type;
        },

        isMultiSelect() {
            let registrationFieldConfiguration = this.description.registrationFieldConfiguration
            
            if (this.is('multiselect')) {
                if(registrationFieldConfiguration?.fieldType && this.entitiesFildTypes.includes(registrationFieldConfiguration?.fieldType)) {
                    const config = registrationFieldConfiguration?.config;
                    return (config?.viewMode === 'tag') || (!config?.viewMode && this.description.optionsOrder?.length > 15);
                } else {
                    return true;
                }
            } else if (this.is('checklist')) {
                const config = registrationFieldConfiguration?.config;
                return (config?.viewMode === 'tag') || (!config?.viewMode && this.description.optionsOrder?.length > 15);
            } else {
                return false;
            }
        },

        isReadonly() {
            const userPermission = this.entity.currentUserPermissions?.modifyReadonlyData;
            const lockedFieldSeals = this.entity.__lockedFieldSeals;

            if(this.entity.__objectType == "registration" && this.description.registrationFieldConfiguration) {
                const registrationConfig = this.description.registrationFieldConfiguration;
                
                if(registrationConfig.fieldType == 'agent-owner-field' && registrationConfig.config?.entityField) {
                    const agentFieldName = registrationConfig.config.entityField;
                    
                    if($DESCRIPTIONS.agent && $DESCRIPTIONS.agent[agentFieldName]) {
                        const agentDescription = $DESCRIPTIONS.agent[agentFieldName];
                        
                        if(agentDescription.readonly) {
                            this.readonly = !(userPermission || !this.value);
                            return this.readonly;
                        }
                    }
                }
            }

            if(this.description.readonly) {
                this.readonly = !(userPermission || !this.value);
            }

            if(lockedFieldSeals && lockedFieldSeals[this.prop]) {
                this.readonly = true;
            }

            const lockedFields = this.entity.__lockedFields || [];

            if(lockedFields.includes(this.prop)) {
                this.readonly = true;
            }

            return this.readonly;
        },

        addRow() {
            if (this.is('custom-table')) {
                // Garantir que é um array
                if (!Array.isArray(this.entity[this.prop])) {
                    this.entity[this.prop] = [];
                }
                
                const maxRows = this.description.registrationFieldConfiguration?.config?.maxRows;
                
                if (!maxRows || maxRows <= 0 || this.entity[this.prop].length < maxRows) {
                    this.entity[this.prop].push({});
                    // NÃO chama updateTableData() aqui - apenas adiciona a linha
                }
            }
        },

        removeRow(index) {
            if (this.is('custom-table')) {
                // Garantir que é um array
                if (!Array.isArray(this.entity[this.prop])) {
                    this.entity[this.prop] = [];
                    return;
                }
                
                const minRows = this.description.registrationFieldConfiguration?.config?.minRows || 0;
                
                if (this.entity[this.prop].length > minRows) {
                    this.entity[this.prop].splice(index, 1);
                    this.updateTableData();
                }
            }
        },

        updateTableData() {
            if (this.is('custom-table')) {
                // Cancelar save anterior
                if (this._saveTimeout) {
                    clearTimeout(this._saveTimeout);
                }
                
                // Aguardar 2 segundos antes de salvar (debounce)
                this._saveTimeout = setTimeout(() => {
                    // CRÍTICO: Criar uma cópia SIMPLES do array, sem Proxy
                    const plainData = this.entity[this.prop]
                        .filter(row => row && typeof row === 'object' && !Array.isArray(row))
                        .map(row => {
                            const plainRow = {};
                            for (const key in row) {
                                if (key.substring(0, 2) !== '$$') {
                                    plainRow[key] = row[key];
                                }
                            }
                            return plainRow;
                        });
                    
                    // Substituir por objetos simples
                    this.entity[this.prop] = plainData;
                    
                    // CRÍTICO: Forçar __originalValues para [] para garantir que data(true) envie tudo
                    if (!this.entity.__originalValues) {
                        this.entity.__originalValues = {};
                    }
                    this.entity.__originalValues[this.prop] = [];
                    
                    // Garantir que o campo seja marcado como modificado
                    if (!this.entity.__changedKeys) {
                        this.entity.__changedKeys = [];
                    }
                    if (!this.entity.__changedKeys.includes(this.prop)) {
                        this.entity.__changedKeys.push(this.prop);
                    }
                    
                    // Salvar
                    this.entity.save().then(() => {
                        this._customTableSaveTimeout = null;
                    });
                }, 2000);
            }
        }
    },
});