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
        return {
            selectAll: false,
            fields: [
                { selected: false },
                { selected: false },
            ]
        };
    },

    watch: {
        selectAll: function (value) {
            this.fields.forEach(function (field) {
                field.selected = value;
            });
        },

        fields: {
            deep: true,
            handler: function () {
                var allSelected = this.fields.every(function (field) {
                    return field.selected;
                });
                this.selectAll = allSelected;
            }
        },
    },

    computed: {
        relations(){
           return this.entity.agentRelations["@support"];
        }
    },
    
    methods: {
        addAgent(agent) {
            this.entity.addRelatedAgent('@support',agent);
        },
        removeAgent(agent) {
            this.entity.removeAgentRelation('@support',agent);
        },
        send(){
        },
    },
});
