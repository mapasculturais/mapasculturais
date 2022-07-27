app.component('entity-files-list', {
    template: $TEMPLATES['entity-files-list'],
    emits: [],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-files-list')
        return { text }
    },

    created() {

    },

    computed: {
        files: () => {
            /* return this.entity.files?.[this.group] || [] */
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        group: {
            type: String,
            required: true
        },
        title: {
            type: String,
            required: true
        }
    },
    
    methods: {
    },
});
