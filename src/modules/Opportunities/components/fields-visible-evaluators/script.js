app.component('fields-visible-evaluators', {
    template: $TEMPLATES['fields-visible-evaluators'],

    props: {

    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        return { hasSlot }
    },

    beforeCreate() { },
    created() { },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() { },
    unmounted() { },

    data() {
        return {

        }
    },

    computed: {
        fields() {
            return $MAPAS.config.fieldsToEvaluate;
        }
    },

    methods: {

    },
});
