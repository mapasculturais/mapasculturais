app.component('mc-collapse', {
    template: $TEMPLATES['mc-collapse'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-collapse')
        return { text, hasSlot }
    },

    data() {
        return {
            expanded: false,
        }
    },

    computed: {
        icon() {
            return this.expanded ? 'triangle-up' : 'triangle-down';
        }
    },
    
    methods: {
        toggle() {
            this.expanded = !this.expanded;
        },
        close() {
            this.expanded = false;
        }
    },
});
