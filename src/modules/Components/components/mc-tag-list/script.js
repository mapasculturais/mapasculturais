app.component('mc-tag-list', {
    template: $TEMPLATES['mc-tag-list'],
    emits: ['remove'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-tag-list')
        return { text }
    },
    
    data() {
        return {}
    },

    props: {
        editable: {
            type: Boolean,
            default: false,
        },
        classes: {
            type: String,
            required: false,
        },
        tags: {
            type: [Array, Object],
            required: true,
        },
        labels: {
            type: [Array, Object],
            default: null
        }
    },

    methods: {
        remove(tag) {
            const tags = this.tags;
            const indexOf = tags.indexOf(tag);
            tags.splice(indexOf,1);
            this.$emit('remove', tag);
        },
    }
});
