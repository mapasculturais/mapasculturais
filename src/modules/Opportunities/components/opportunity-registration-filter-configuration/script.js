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
        }
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
            selectedField: '',
            selectedConfigs: [],
            tagsList: [],
            configs: {},
            localExcludeFields: []
        }
    },

    computed: {
        filteredFields() {
            return {
                categories: this.registrationCategories.filter(cat => !this.excludeFields.includes('category')),
                proponentTypes: this.registrationProponentTypes.filter(type => !this.excludeFields.includes('proponentType')),
                ranges: this.registrationRanges.filter(range => !this.excludeFields.includes('range'))
            };
        },

        fillTagsList() {
            let groupData = this.defaultValue || {};
            this.tagsList = [];

            if (!this.isGlobal) {
                groupData = this.getAgentData() || {};
            }

            Object.entries(groupData).forEach(([key, values]) => {
                if (Array.isArray(values)) {
                    values.forEach(value => {
                        const tag = `${this.dictTypes(key)}: ${value}`;
                        if (!this.tagsList.includes(tag)) {
                            this.tagsList.push(tag);
                        }
                    });
                }
            });

            return this.tagsList;
        }
    },

    methods: {
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

        addConfig() {
            this.configs = this.defaultValue ?? {};

            if (!this.isGlobal) {
                this.evaluatorConfig();
            } else {
                this.globalConfig();
            }

            this.$emit('update:defaultValue', this.configs);
            this.loadExcludeFields();

            this.save();
        },

        async save() {
            await this.entity.save();
        },

        dictTypes(type, reverse = false) {
            const typeDictionary = {
                'category': 'Categoria',
                'proponentType': 'Tipos do proponente',
                'range': 'Faixa/Linha',
                'distribution': 'Distribuição'
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

            if (this.defaultValue && this.defaultValue[key]) {
                const configArray = this.defaultValue[key];

                const index = configArray.indexOf(value);
                if (index !== -1) {
                    configArray.splice(index, 1);
                }

                this.save();
            }
        },

        globalConfig() {
            if (!this.configs[this.selectedField]) {
                this.configs[this.selectedField] = [];
            }

            this.selectedConfigs.forEach(config => {
                if (!this.configs[this.selectedField].includes(config)) {
                    this.configs[this.selectedField].push(config);
                }
            });
        },

        evaluatorConfig() {
            const agentId = this.infoReviewer.agent.id;

            if (!this.configs[agentId]) {
                this.configs[agentId] = {};
            }

            if (!this.configs[agentId][this.selectedField]) {
                this.configs[agentId][this.selectedField] = []; 
            }

            this.configs[agentId][this.selectedField] = [...this.selectedConfigs];

            this.selectedConfigs.forEach(config => {
                if (!this.configs[agentId][this.selectedField].includes(config)) {
                    this.configs[agentId][this.selectedField].push(config);
                }
            });

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
                        }
                    }
                });
            });

        },

        getAgentData() {
            const agentId = this.infoReviewer?.agent?.id;

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

            return agentData;
        }
    },

    mounted() {
        this.loadExcludeFields();
    }
}); 
