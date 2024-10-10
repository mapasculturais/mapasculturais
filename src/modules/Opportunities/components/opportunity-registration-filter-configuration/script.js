app.component('opportunity-registration-filter-configuration', {
    template: $TEMPLATES['opportunity-registration-filter-configuration'],
    emits: ['updateExcludeFields'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        groupName: {
            type: String,
            required: true
        },

        excludeFields: {
            type: Array,
            required: false,
            default: () => []
        }
    },

    data() {
        return {
            registrationCategories: $MAPAS.opportunityPhases[0].registrationCategories ?? [],
            registrationProponentTypes: $MAPAS.opportunityPhases[0].registrationProponentTypes ?? [],
            registrationRanges: $MAPAS.opportunityPhases[0].registrationRanges?.map(range => range.label) ?? [],
            selectedField: '',
            selectedConfigs: [],
            tagsList: []
        }
    },

    computed: {
        filteredFields() {
            return {
                categories: this.registrationCategories.filter(cat => !this.excludeFields.includes('category')),
                proponentTypes: this.registrationProponentTypes.filter(type => !this.excludeFields.includes('proponentType')),
                ranges: this.registrationRanges.filter(range => !this.excludeFields.includes('range'))
            };
        }
    },

    methods: {
        isFieldExcluded(field) {
            return this.excludeFields.includes(field);
        },
        
        addConfig() {
            if (!this.entity.registrationFilterConfig) {
                this.entity.registrationFilterConfig = {};
            }
        
            if (!this.entity.registrationFilterConfig[this.groupName]) {
                this.entity.registrationFilterConfig[this.groupName] = {};
            }
        
            if (!this.entity.registrationFilterConfig[this.groupName][this.selectedField]) {
                this.entity.registrationFilterConfig[this.groupName][this.selectedField] = [];
            }
        
            this.entity.registrationFilterConfig[this.groupName][this.selectedField] = [...this.selectedConfigs];

            this.$emit('updateExcludeFields', this.selectedField);

            this.fillTagsList();
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

        fillTagsList() {
            if (!this.entity?.registrationFilterConfig || !this.entity.registrationFilterConfig[this.groupName]) {
                return;
            }

            let groupData = this.entity?.registrationFilterConfig[this.groupName] || {};
            this.tagsList = [];
            
            Object.entries(groupData).forEach(([key, values]) => {
                if (Array.isArray(values)) {
                    values.forEach(value => {
                        this.tagsList.push(`${this.dictTypes(key)}: ${value}`);
                    });
                }
            });
        },

        removeTag(tag) {
            const [displayKey, value] = tag.split(': ');
            const key = this.dictTypes(displayKey, true);
        
            if (this.entity.registrationFilterConfig[this.groupName] && this.entity.registrationFilterConfig[this.groupName][key]) {
                const configArray = this.entity.registrationFilterConfig[this.groupName][key];
        
                const index = configArray.indexOf(value);
                if (index !== -1) {
                    configArray.splice(index, 1);
                }
        
                this.fillTagsList();
                this.save();
            }
        }
    },

    mounted() {
        this.fillTagsList();
    }
}); 
