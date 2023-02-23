app.component('mc-stepper-vertical', {
    template: $TEMPLATES['mc-stepper-vertical'],

    props: {
        items: {
            type: Array,
            required: true
        },

        allowMultiple: {
            type: Boolean,
            default: false
        },

        opened: {
            type: Number
        }
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-stepper-vertical')
        return { text }
    },

    data() {
        const activeItems = {};
        if (this.opened !== undefined) {
            activeItems[this.opened] = true;
        }

        return {
            activeItems
        }
    },

    computed: {
        steps() {
            return this.items.map((item, index) => {
                return {
                    item,
                    index,
                    number: index+1,
                    open: () => this.open(index),
                    close: () => this.close(index),
                    toggle: () => this.toggle(index),
                    active: !!this.activeItems[index]
                }
            });
        }
    },
    
    methods: {
        open(index) {
            if (!this.allowMultiple) {
                this.activeItems = {};
            }
            this.activeItems[index] = true;
        },

        close(index) {
            delete this.activeItems[index];
        },

        toggle(index) {
            if (this.activeItems[index]) {
                this.close(index);
            } else {
                this.open(index);
            }
        }
    },
});
