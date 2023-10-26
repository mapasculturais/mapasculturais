
app.component('mc-title', {
    template: $TEMPLATES['mc-title'],

    props: {
        tag: {
            type: String,
            default: 'h2',
            validator: (value) => ['h1', 'h2', 'h3', 'h4'].includes(value)
        },

        size: {
            type: String,
            default: 'medium',
            validator: (value) => ['big', 'medium', 'small'].includes(value)
        },

        shortLength: {
            type: Number,
            default: 20
        },

        longLength: {
            type: Number,
            default: 30
        }
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
       
        const length = this.$refs.title.textContent.trim().length;
        if(length > this.longLength) {
            this.classes.push('mc-title--long');
            
        } else if (length < this.shortLength) {
            this.classes.push('mc-title--short');
        }

        if(this.size != 'medium') {
            this.classes.push('mc-title--' + this.size);
        }
    }
});
