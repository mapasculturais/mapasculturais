
app.component('mc-title', {
    template: $TEMPLATES['mc-title'],
// 
    props: {
        tag: {
            type: String,
            default: 'h2'
        },
        local: {
            type: String,
            default: 'single'
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
        // this.classes.push(this.tag);
        if(length > 30) {
            const tag = this.tag;
            // this.classes.push('mc-title__'+this.tag+'--short');
            this.classes.push('mc-title__'+this.tag+'--long');
            

        } else if (length < 20) {
            this.classes.push('mc-title__'+this.tag+'--short');

        }
    }
});
