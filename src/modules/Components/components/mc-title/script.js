
app.component('mc-title', {
    template: $TEMPLATES['mc-title'],

    props: {
        tag: {
            type: String,
            default: 'h2'
        },
    },
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-title')
        return { text, hasSlot }
    },

    data() {
        return {classes:[]};
    },

    mounted() {
        const length = this.$refs.title.textContent.length;

        if(length > 30) {
            this.classes.push('mc-title--long');
        } else if (length < 20) {
            this.classes.push('mc-title--short');
        }
    }
});
