app.component('mc-select', {
    template: $TEMPLATES['mc-select'],
    emits: ['changeOption'],

    props: {
        defaultValue: {
            type: [String, Number],
            required: false,
            default: null,
        },
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-select')
        return { text, hasSlot }
    },

    mounted() {
        const childrens = this.$refs.options.children;
            for (const [index, child] of Object.entries(childrens)) {
                child.addEventListener("click", (e) => this.selectOption(e));

                if (this.defaultValue && child.value == this.defaultValue) {
                    this.selected = {
                        text: child.text,
                        value: child.value,
                    }
                }
            };
        },

    beforeUpdate() {
        if (!this.defaultValue) {
            this.selected = {
                text:  null,
                value: null,
            }
        }
    },

    data() {
        return {
            selected: {
                text: null,
                value: null,
            },
            open: false,
        };
    },

    methods: {
        toggleSelect() {
            this.open = !this.open
        },

        selectOption(event) {
            this.toggleSelect();
            const childrens = this.$refs.options.children;
            if (this.selected.text != event.target.text) {
                for (const [index, child] of Object.entries(childrens)) {
                    if (child.text == event.target.text) {
                        this.selected = {
                            text: event.target.text,
                            value: event.target.value,
                        }
                    }
                };
                this.$emit("changeOption", this.selected);
            }
        },
    },
});
