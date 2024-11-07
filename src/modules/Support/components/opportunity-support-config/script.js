app.component('opportunity-support-config', {
    template: $TEMPLATES['opportunity-support-config'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('opportunity-support-config')
        return { text, messages }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    data() {
        const fields = $MAPAS.config.opportunitySupportConfig;
    
        return {
            fields,
            allPermissions: null,
            selectAll: false,
            selectedFields: {},
            permissionsFields: {},
            categoryFilter: null,
            proponentFilter: null,
            rangeFilter: null,
            keyword: "",
        };
    },

    mounted() {
        this.fields.forEach(field => {
            this.selectedFields[field.ref] = false;
        });
    },

    computed: {
        relations() {
            return this.entity.agentRelations["@support"] || [];
        },
        
        query() {
            let ids = [];
            let query = {};

            for (let relation of this.relations) {
                ids.push(relation.agent.id);
            }
            if (ids.length > 0) {
                query['id'] = `!IN(${ids})`;
            }
            return query;
        },

        permissions() {
            return [
                {
                    value: '',
                    label: this.text('Sem permissão'),
                },
                {
                    value: 'ro',
                    label: this.text('Visualizar'),
                },
                {
                    value: 'rw',
                    label: this.text('Modificar'),
                }
            ]
        },

        hasSelectedField() {
            return Object.values(this.selectedFields).some(value => value === true);
        },

        categories() {
            if (this.entity.registrationCategories.length > 0) {                
                let categories = this.entity.registrationCategories;

                if (!categories.includes('Todos')) {
                    categories.unshift('Todos');
                    this.categoryFilter = 'Todos';
                }

                return categories.map(function(category) {
                    return {
                        label: category,
                        value: category,
                    }
                });
            } else {
                return false;
            }
        },
        
        proponentTypes() {
            if (this.entity.registrationProponentTypes.length > 0) {                
                let proponentTypes = this.entity.registrationProponentTypes;

                if (!proponentTypes.includes('Todos')) {
                    proponentTypes.unshift('Todos');
                    this.proponentFilter = 'Todos';
                }

                return proponentTypes.map(function(proponentType) {
                    return {
                        label: proponentType,
                        value: proponentType,
                    }
                });
            } else {
                return false;
            }
        },

        ranges() {

            if (this.entity.registrationRanges.length > 0) {                
                let ranges = this.entity.registrationRanges;

                if (!ranges.includes('Todos')) {
                    ranges.unshift('Todos');
                    this.rangeFilter = 'Todos';
                }
                
                return ranges.map(function(range) {
                    return {
                        label: range.label ?? range,
                        value: range.label ?? range,
                    }
                });
            } else {
                return false;
            }
        },

        sortedFields() {
            return this.fields.toSorted((a, b) => {
                console.log(a.step, b.step);
                if (a.step?.displayOrder === b.step?.displayOrder) {
                    return Math.sign(a.displayOrder - b.displayOrder);
                } else {
                    return Math.sign(a.step?.displayOrder - b.step?.displayOrder);
                }
            });
        },

        filteredFields() {
            let fields = this.sortedFields;
            const category = this.categoryFilter; 
            const proponent = this.proponentFilter;
            const range = this.rangeFilter;
            const keyword = this.keyword

            if (category && category != 'Todos') {
                fields = fields.filter(function(field) {
                    if (field.categories.length <= 0 || (field.categories.length > 0 && field.categories.includes(category))) {
                        return field;
                    }
                });
            }

            if (proponent && proponent != 'Todos') {
                fields = fields.filter(function(field) {
                    if (field.proponentTypes.length <= 0 || (field.proponentTypes.length > 0 && field.proponentTypes.includes(proponent))) {
                        return field;
                    }
                });
            }

            if (range && range != 'Todos') {
                fields = fields.filter(function(field) {
                    if (field.registrationRanges.length <= 0 || (field.registrationRanges.length > 0 && field.registrationRanges.includes(range))) {
                        return field;
                    }
                });
            }

            if (keyword) {
                fields = fields.filter(function(field) {
                    if (field.title.startsWith(keyword) || field.id.toString().startsWith(keyword)) {
                        return field;
                    }
                });
            }

            return fields;
        }
    },

    methods: {
        getConditionalField(field) {
            let conditionalField  = null;
            if(field.conditional) {
                this.filteredFields.filter((item) => {
                    if(field.conditionalField === item.ref) {
                        conditionalField = item.id;
                        return
                    }
                });

            }
            return conditionalField
        },

        filterKewWord() {
            this.filteredFields;
        },

        getFieldType(field) {
            if(field.ref.startsWith('field_')) {
                return 'text';
            }

            return 'file';
        },
        
        set(option) {
            this.categoryFilter = option.text;
        },

        addAgent(agent) {
            this.entity.addRelatedAgent('@support', agent);
        },

        removeAgent(agent) {
            this.entity.removeAgentRelation('@support', agent);
        },

        setPerssion(event, field) {
            this.permissionsFields[field.ref] = event.value;
        },

        setAllPerssions(event) {
            for (let field in this.selectedFields) {
                if (this.selectedFields[field]) {
                    this.permissionsFields[field] = event.value;
                    this.entity.agentRelations["@support"][0].metadata.registrationPermissions[field] = event.value
                }
            }
        },

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.fields.forEach(field => {
                    this.selectedFields[field.ref] = true;
                });
            } else {
                this.clearSelectedFields();
            }
        },

        clearSelectedFields() {
            this.fields.forEach(field => {
                this.selectedFields[field.ref] = false;
            });
        },
        clearFilters() {
            this.categoryFilter = null;
            this.proponentFilter = null;
            this.rangeFilter = null;
            this.keyword = "";
        },

        countRegistrationTypes(field) {
            let count = 0;
            if (field.categories) count++;
            if (field.registrationRanges) count++;
            if (field.proponentTypes) count++;
            return count;
        },

        send(modal, relation) {
            let api = new API();
            let url = Utils.createUrl('suporte', 'opportunityPermissions', { agentId: relation.agent.id, opportunityId: this.entity.id });

            api.PUT(url, this.permissionsFields)
                .then(response => response.json())
                .then(data => {
                    for (let relation of this.entity.agentRelations["@support"]) {
                        if (relation.id == data.id) {
                            relation.metadata = data.metadata;
                        }
                    }
                    this.clearSelectedFields();
                    this.clearFilters();
                    this.messages.success(this.text('Permisão enviada com sucesso'));
                    this.selectAll = false;
                    this.allPermissions = null;
                    modal.close();
                })
        },
    },
});
