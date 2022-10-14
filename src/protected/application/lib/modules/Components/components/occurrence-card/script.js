app.component('occurrence-card', {
    template: $TEMPLATES['occurrence-card'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('occurrence-card')
        return { text }
    },

    data() {
        return {
            event: this.occurrence.event,
            space: this.occurrence.space,
        }
    },

    props: {
        occurrence: {
            type: Object,
            required: true
        },

        hideSpace: {
            type: Boolean,
            default: false
        }
    },

    computed: {
        seals() {
            return (this.occurrence.event.seals.length > 0 ? this.occurrence.event.seals.slice(0, 2) : false);
        },
        areas() {
            return (Array.isArray(this.occurrence.event.terms.area) ? this.occurrence.event.terms.area.join(", ") : false);
        },
        tags() {
            return (Array.isArray(this.occurrence.event.terms.tag) ? this.occurrence.event.terms.tag.join(", ") : false);
        },
        linguagens() {
            return (Array.isArray(this.occurrence.event.terms.linguagem) ? this.occurrence.event.terms.linguagem.join(", ") : false);
        }
    },
    
    methods: {
    },
});
