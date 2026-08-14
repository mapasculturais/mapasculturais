app.component('request-agent-avatar', {
template: $TEMPLATES['request-agent-avatar'],

    setup(props) { },
    mounted() { },

    data() {
        return {}
    },

    computed: {
        targetEntity() {
            return this.agent || this.entity.owner;
        },

        errorMessages() {
            if (!this.errorKey) {
                return [];
            }

            return this.entity.__validationErrors?.[this.errorKey] || [];
        },
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        agent: {
            type: Entity,
            required: false,
            default: null
        },
        errorKey: {
            type: String,
            default: 'avatar'
        }
    },

    methods: {
        hasErrors() {
            let errors = this.errorMessages;
            if(errors.length > 0){
                return true;
            } else {
                return false;
            }
        },
    },

});
