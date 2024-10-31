app.component('opportunity-registration-filter-configuration', {
    template: $TEMPLATES['opportunity-registration-filter-configuration'],
    emits: ['updateExcludeFields', 'update:defaultValue'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        excludeFields: {
            type: Array,
            required: false,
            default: () => []
        },

        defaultValue: {
            type: [Array, Object],
            default: null,
        },

        isGlobal: {
            type: Boolean,
            default: false
        },

        infoReviewer: {
            type: Object,
            required: false
        },

        useDistributionField: {
            type: Boolean,
            default: false
        },

        isSection: {
            type: Boolean,
            default: false
        },

        isCriterion: {
            type: Boolean,
            default: false
        },

        titleModal: {
            type: String,
            default: ''
        },
    },

    watch: {
        defaultValue(newValue, oldValue) {
            this.$emit('update:defaultValue', newValue);
        }
    },

    data() {
        return {
            registrationCategories: $MAPAS.opportunityPhases[0].registrationCategories ?? [],
            registrationProponentTypes: $MAPAS.opportunityPhases[0].registrationProponentTypes ?? [],
            registrationRanges: $MAPAS.opportunityPhases[0].registrationRanges?.map(range => range.label) ?? [],
            registrationSelectionFields: $MAPAS.config.fetchSelectFields?.reduce((acc, fields) => {
                acc[fields.title] = fields.fieldOptions;
                return acc;
            }, {}) ?? {},
            selectedField: '',
            selectedConfigs: [],
            selectedDistribution: '',
            tagsList: [],
            configs: {},
            localExcludeFields: [],
            isSelected: false,
        }
    },

    computed: {
        filteredFields() {
            if (this.isCriterion) {
                const section = this.entity.sections.find(section => section.id === this.defaultValue.sid);
                return {
                    categories: section.categories,
                    proponentTypes: section.proponentTypes,
                    ranges: section.ranges
                }
            } else {
                return {
                    categories: this.registrationCategories.filter(cat => !this.excludeFields.includes('category')),
                    proponentTypes: this.registrationProponentTypes.filter(type => !this.excludeFields.includes('proponentType')),
                    ranges: this.registrationRanges.filter(range => !this.excludeFields.includes('range'))
                };
            }
        },

        fillTagsList() {
            let groupData = this.defaultValue || {};
            this.tagsList = [];

            if (!this.isGlobal && !this.isSection && !this.isCriterion) {
                groupData = this.getAgentData() || {};
            }

            Object.entries(groupData).forEach(([key, values]) => {
                if ((this.isSection && this.excludeFields.includes(key)) || (this.isCriterion && this.excludeFields.includes(key))) {
                    return;
                }
                if (Array.isArray(values)) {
                    values.forEach(value => {
                        const tag = `${this.dictTypes(key)}: ${value}`;
                        if (!this.tagsList.includes(tag)) {
                            this.tagsList.push(tag);
                        }
                    });
                } else {
                    const tag = `${this.dictTypes(key)}: ${values}`;
                    if (!this.tagsList.includes(tag)) {
                        this.tagsList.push(tag);
                    }
                }
            });

            return this.tagsList.sort();
        }
    },

    methods: {
        handleSelection() {
            this.isSelected = !!this.selectedField; 
        },

        loadExcludeFields() {
            this.localExcludeFields = [];

            const tags = this.fillTagsList || [];
            
            tags.forEach(tag => {
                const [displayKey] = tag.split(': ');
                const key = this.dictTypes(displayKey, true);
            
               this.localExcludeFields.push(key);
            });
            
            this.$emit('updateExcludeFields', this.localExcludeFields);
        },

        isFieldExcluded(field) {
            return this.excludeFields.includes(field);
        },

        addConfig(modal) {
            this.configs = this.defaultValue ?? {};

            if (this.isGlobal) {
                this.globalConfig();
            } else if(this.isSection) {
                this.sectionConfig();
            } else if (this.isCriterion) {
                this.criteriaConfig();
            } else {
                this.evaluatorConfig();
            }

            this.selectedField = '';
            this.$emit('update:defaultValue', this.configs);
            this.loadExcludeFields();
            this.save();
            modal.close();
        },

        async save() {
            this.entity.save();
        },

        dictTypes(type, reverse = false) {
            const typeDictionary = {
                'category': 'Categoria',
                'categories': 'Categorias',
                'proponentType': 'Tipos do proponente',
                'proponentTypes': 'Tipos de proponente',
                'range': 'Faixa/Linha',
                'ranges': 'Faixas/Linhas',
                'distribution': 'Distribuição',
            };

            if (reverse) {
                const reversedDictionary = Object.fromEntries(Object.entries(typeDictionary).map(([key, value]) => [value, key]));
                return reversedDictionary[type] || type;
            }


            return typeDictionary[type] || type;
        },

        removeTag(tag) {
            const [displayKey, value] = tag.split(': ');
            const key = this.dictTypes(displayKey, true);

            if(this.isGlobal) {
                this.removeGlobal(key, value);
            } else if (this.isSection) {
                this.removeSectionConfig(key, value);
            } else if (this.isCriterion) {
                this.removeCriteriaConfig(key, value);
            } else {
                this.removeIndividual(key, value);
            }

            this.loadExcludeFields();
            this.save();
        },

        removeGlobal(key, value) {
            if (this.defaultValue && this.defaultValue[key]) {
                this.selectedConfigs = this.selectedConfigs.filter(config => config != value);
                
                const configArray = this.defaultValue[key];
                const index = configArray.indexOf(value);
                if (index !== -1) {
                    this.defaultValue[key].splice(index, 1);
                }                
            }
        },

        removeIndividual(key, value) {
            const agentId = this.infoReviewer.agentUserId;
            this.selectedConfigs = this.selectedConfigs.filter(config => config != value);

            if (!agentId) {
                return;
            }

            if (key === 'distribution') {
                if (this.defaultValue && this.defaultValue[agentId] && this.defaultValue[agentId][key] === value) {
                    delete this.defaultValue[agentId][key];
                    this.$emit('update:defaultValue', this.defaultValue);
                }
        
                const agentData = this.getAgentData();
                if (agentData && agentData[key] === value) {
                    delete agentData[key];
                    this.updateAgentData(agentId, key, null);
                }
        
                return;
            }
        
            if (this.defaultValue && this.defaultValue[agentId] && Array.isArray(this.defaultValue[agentId][key])) {
                const configArray = this.defaultValue[agentId][key];
                const index = configArray.indexOf(value);
                
                if (index !== -1) {
                    configArray.splice(index, 1);
                }
        
                let newDefaultValue = configArray

                if (configArray.length === 0) {
                    delete this.defaultValue[agentId][key];
                    newDefaultValue = this.defaultValue;
                }
        
                this.$emit('update:defaultValue', newDefaultValue);
                return;
            }
        
            const agentData = this.getAgentData();
        
            if (agentData && agentData[key]) {
                const configArray = agentData[key];
        
                const index = configArray.indexOf(value);
                
                if (index !== -1) {
                    configArray.splice(index, 1);
                }
        
                if (configArray.length === 0) {
                    delete agentData[key];
                }
        
                this.updateAgentData(agentId, key, agentData[key]);
            }
        },
        
        updateAgentData(agentId, key, value) {
            if (!this.entity.fetchCategories[agentId]) {
                this.entity.fetchCategories[agentId] = [];
            }
        
            if (key === 'category') {
                this.entity.fetchCategories[agentId] = value || [];
            } else if (key === 'proponentType') {
                this.entity.fetchProponentTypes[agentId] = value || [];
            } else if (key === 'range') {
                this.entity.fetchRanges[agentId] = value || [];
            } else if (key === 'distribution') {
                this.entity.fetch[agentId] = value || '';
            } else {
                this.entity.fetchSelectionFields[agentId][key] = value || {};

                if (Object.keys(this.entity.fetchSelectionFields[agentId][key]).length === 0) {
                    delete this.entity.fetchSelectionFields[agentId][key];
                }
            }
        },

        globalConfig() {
            if (!this.configs[this.selectedField]) {
                this.configs[this.selectedField] = [];
            }

            let newConfig = [];
            this.selectedConfigs.forEach(config => {;
                if (!this.configs[this.selectedField].includes(config)) {
                    newConfig.push(config);
                } else {
                    if (newConfig.includes(config)) {
                        newConfig = newConfig.remove(config);
                    }
                }
            });

            this.configs[this.selectedField] = newConfig;             
        },

        evaluatorConfig() {
            const agentId = this.infoReviewer.agentUserId;
            const options = ['category', 'range', 'proponentType', 'distribution'];

            if (!this.configs[agentId]) {
                this.configs[agentId] = {};
            }

            if (!this.configs[agentId][this.selectedField]) {
                this.configs[agentId][this.selectedField] = this.selectedField == 'distribution' ? '' : []; 
            }

            this.configs[agentId][this.selectedField] = this.selectedField == 'distribution' ? this.selectedDistribution : [...this.selectedConfigs];

            if (this.selectedField !== 'distribution') {
                this.selectedConfigs.forEach(config => {
                    if (!this.configs[agentId][this.selectedField].includes(config)) {
                        this.configs[agentId][this.selectedField].push(config);
                    }
                });
            }

            Object.entries(this.configs).forEach(([agentId, values]) => {
                Object.entries(values).forEach(([key, configs]) => {
                    if (Array.isArray(configs)) {
                        if (key === 'proponentType') {
                            if (!this.entity.fetchProponentTypes[agentId]) {
                                this.entity.fetchProponentTypes[agentId] = [];
                            }

                            configs.forEach(config => {
                                if (!this.entity.fetchProponentTypes[agentId].includes(config)) {
                                    this.entity.fetchProponentTypes[agentId].push(config);
                                }
                            });

                        } else if (key === 'category') {
                            if (!this.entity.fetchCategories[agentId]) {
                                this.entity.fetchCategories[agentId] = [];
                            }

                            configs.forEach(config => {
                                if (!this.entity.fetchCategories[agentId].includes(config)) {
                                    this.entity.fetchCategories[agentId].push(config);
                                }
                            });

                        } else if (key === 'range') {
                            if (!this.entity.fetchRanges[agentId]) {
                                this.entity.fetchRanges[agentId] = [];
                            }

                            configs.forEach(config => {
                                if (!this.entity.fetchRanges[agentId].includes(config)) {
                                    this.entity.fetchRanges[agentId].push(config);
                                }
                            });
                        } else if (!options.includes(this.selectedField)) {
                            if(!this.entity.fetchSelectionFields) {
                                this.entity.fetchSelectionFields = {};
                            }

                            if (!this.entity.fetchSelectionFields[agentId]) {
                                this.entity.fetchSelectionFields[agentId] = {};
                            }
                            
                            if (!this.entity.fetchSelectionFields[agentId][this.selectedField]) {
                                this.entity.fetchSelectionFields[agentId][this.selectedField] = [];
                            }
                            
                            configs.forEach(config => {
                                if (!this.entity.fetchSelectionFields[agentId][this.selectedField].includes(config)) {
                                    this.entity.fetchSelectionFields[agentId][this.selectedField].push(config);
                                }
                            });
                        }
                    } else {
                        if(key == 'distribution') {
                            if (!this.entity.fetch[agentId]) {
                                this.entity.fetch[agentId] = '';
                            }

                            this.entity.fetch[agentId] = configs;
                        }
                    }
                });
            });
        },

        getAgentData() {
            const agentId = this.infoReviewer?.agentUserId;

            if (!agentId) {
                return null;
            }

            let agentData = {};

            if (this.entity.fetchCategories && this.entity.fetchCategories[agentId]) {
                agentData['category'] = this.entity.fetchCategories[agentId];
            }

            if (this.entity.fetchProponentTypes && this.entity.fetchProponentTypes[agentId]) {
                agentData['proponentType'] = this.entity.fetchProponentTypes[agentId];
            }

            if (this.entity.fetchRanges && this.entity.fetchRanges[agentId]) {
                agentData['range'] = this.entity.fetchRanges[agentId];
            } 
            
            if (this.entity.fetch && this.entity.fetch[agentId]) {
               
                agentData['distribution'] = this.entity.fetch[agentId];
            } 
            
            if (this.entity.fetchSelectionFields && this.entity.fetchSelectionFields[agentId]) {
                for (const field in this.entity.fetchSelectionFields[agentId]) {
                    agentData[field] = this.entity.fetchSelectionFields[agentId][field];
                }
            }

            return agentData;
        },

        sectionConfig() {
            let field = '';
            if (this.selectedField == 'category') {
                field = 'categories';
            } else if (this.selectedField == 'proponentType') {
                field = 'proponentTypes';
            } else if (this.selectedField == 'range') {
                field = 'ranges';
            }
    
            if (!this.configs[field]) {
                this.configs[field] = [];
            }

            this.selectedConfigs.forEach(config => {
                if (!this.configs[field].includes(config)) {
                    this.configs[field].push(config);
                }
            });

            this.selectedConfigs = [];
        },

        removeSectionConfig(key, value) {
            const section = this.entity.sections.find(section => section.id === this.defaultValue.id);
            const criterias = this.entity.criteria.filter(crit => crit.sid === section.id);  
            if (section[key]) {
                section[key] = section[key].filter(config => config != value);
            }
            if (criterias.find(crit => crit[key])) {
                criterias.forEach(crit => {
                    crit[key] = crit[key].filter(config => config != value);
                });
            }
        },

        criteriaConfig() {
            let field = '';
            if (this.selectedField == 'category') {
                field = 'categories'
            }

            if (this.selectedField == 'proponentType') {
                field = 'proponentTypes'
            }

            if (this.selectedField == 'range') {
                field = 'ranges'
            }
    
            if (!this.configs[field]) {
                this.configs[field] = [];
            }
            
            this.selectedConfigs.forEach(config => {
                if (!this.configs[field].includes(config)) {
                    this.configs[field].push(config);
                }
            });

            this.selectedConfigs = [];
        },

        removeCriteriaConfig(key, value) {
            const criteria = this.entity.criteria.find(crit => crit.id === this.defaultValue.id);
            if (criteria[key]) {
                criteria[key] = criteria[key].filter(config => config != value);
            }
        },

        showField(type) {
            if (this.isSection) {
                switch (type) {
                    case 'category':
                        return this.registrationCategories.length > 1;
                    case 'proponentType':
                        return this.registrationProponentTypes.length > 1;
                    case 'range':
                        return this.registrationRanges.length > 1;
                }
            } else if (this.isCriterion) {
                const criteria = this.entity.criteria.find(crit => crit.id === this.defaultValue.id);
                const section = this.entity.sections.find(section => section.id === criteria.sid);
                switch (type) {
                    case 'category':
                        return section.categories?.length > 1;
                    case 'proponentType':
                        return section.proponentTypes?.length > 1;
                    case 'range':
                        return section.ranges?.length > 1;
                }
            } else {
                switch (type) {
                    case 'category':
                        return this.registrationCategories.length > 0;
                    case 'proponentType':
                        return this.registrationProponentTypes.length > 0;
                    case 'range':
                        return this.registrationRanges.length > 0;
                }
            }
        },
    },

    mounted() {
        this.loadExcludeFields();
    }
}); 
