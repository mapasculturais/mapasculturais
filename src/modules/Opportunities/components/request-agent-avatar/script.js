app.component('request-agent-avatar', {
template: $TEMPLATES['request-agent-avatar'],

    setup(props) { },
    mounted() { },

    data() {
        return {}
    },

    computed: {},

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    methods: {
        hasErrors() {
            let errors = this.entity.__validationErrors['avatar'] || [];
            if(errors.length > 0){
                return true;
            } else {
                return false;
            }
        },
    },

});