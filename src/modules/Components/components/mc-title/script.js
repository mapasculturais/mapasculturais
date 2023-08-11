
app.component('mc-title', {
    template: $TEMPLATES['mc-title'],

    props: {
        tag: {
            type: String,
            default: 'h2'
        },
        bold: {
            type: Boolean,
            default: false
        },
        semibold: {
            type: Boolean,
            default: false
        },
        uppercase: {
            type: Boolean,
            default: false
        },
        small: {
            type: Boolean,
            default: false
        },
        mobile: {
            type: Boolean,
            default: false
        },
        aligncenter: {
            type: Boolean,
            default: 'false'
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
        // if(length > 30) {
        //     this.classes.push('mc-title--long');
        // } else if (length < 20) {
        //     this.classes.push('mc-title--short');
        // }

        if(this.bold) {
            this.classes.push('bold');
        }
        if(this.semibold) {
            this.classes.push('semibold');
        }
        if(this.uppercase) {
            this.classes.push('uppercase');
        }
        if(this.small) {
            this.classes.push('small');
        }
        if(this.mobile) {
            this.classes.push('mc-title--mobile-'+this.tag);
        }
        if(this.aligncenter) {
            this.classes.push('mc-title--mobile-'+this.tag+'--aligncenter');
        }
           
    }
});
