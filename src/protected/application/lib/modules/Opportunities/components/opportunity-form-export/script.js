app.component('opportunity-form-export', {
    template: $TEMPLATES['opportunity-form-export'],


    emits: [],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-files-list')
        return { text }
    },

    created() {

    },

    computed: {

    },

    props: {
        classes: {
            type: [String, Array, Object],
            required: false
        },

    },

    data() {
    },

    methods: {

    },
});
