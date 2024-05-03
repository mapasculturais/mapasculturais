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
            allPermissions: null,
            selectAll: false,
            selectedFields: {},
            permissionsFields: {},
            fields,
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
        }
    },

    methods: {
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
                    this.messages.success(this.text('Permisão enviada com sucesso'));
                    this.selectAll = false;
                    this.allPermissions = null;
                    modal.close();
                })
        },
    },
});
