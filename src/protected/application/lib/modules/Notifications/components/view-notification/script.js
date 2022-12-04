app.component('view-notification' , {
    template: $TEMPLATES['view-notification'],
    emits: ['create'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('view-notification')
        return { text }
    },
    
    created() {
    },

    data() {
        return {
            entity: null,
            fields: [],
        }
    },

    props: {
        editable: {
            type: Boolean,
            default:true
        },

    },

    computed: {

    },
    
    methods: {

    },
});
