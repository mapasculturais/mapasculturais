app.component('mc-alert', {
    template: $TEMPLATES['mc-alert'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {
        type: {
            type: String,
            required: true
        },
        state: {
            type: Boolean,
            default: true
        },
        closeButton: {
            type: Boolean,
            default: false
        },
        small: {
            type: Boolean,
            default: false,
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-alert')
        return { text, hasSlot }
    },

    data() {
        return {
            showAlert: this.state,
        }
    },
    
    methods: {
        open() {
            if (!this.showAlert) {
                this.showAlert = true;
            }
        },
        close() {
            if (this.showAlert) {
                this.showAlert = false;
            }
        },
        toogle() {
            if (!this.showAlert) {
                this.open();
            } else {
                this.close();
            }
        }
    },
});
