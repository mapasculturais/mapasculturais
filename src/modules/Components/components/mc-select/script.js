app.component('mc-select', {
    template: $TEMPLATES['mc-select'],
    emits: ['changeOption', 'update:defaultValue'],

    props: {
        defaultValue: {
            type: [String, Number],
            default: null,
        },

        placeholder: {
            type: String,
            default: 'Selecione'
        },

        hasGroups: {
            type: Boolean,
            default: false,
        },

        small: {
            type: Boolean,
            default: false,
        },
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-select')
        return { text, hasSlot }
    },

    mounted() {
        setTimeout(() => {
            const options = this.$refs.options.children;            
            for (const [index, option] of Object.entries(options)) {
                
                if (this.hasGroups) {
                    for (const [_index, _option] of Object.entries(option.children)) {
                        _option.addEventListener("click", (e) => this.selectOption(e));
                        this.setDefaultOption(_option);
                    }
                } else {
                    option.addEventListener("click", (e) => this.selectOption(e));
                    this.setDefaultOption(option);
                }
            }

            if (this.defaultValue === null || this.defaultValue === '') {
                this.$refs.selected.innerHTML = this.placeholder;
            }
        });

        document.addEventListener('mousedown', (event) => {
            const select = event.target.closest('.mc-select');
            if (!select) {
                this.open = false
            } else if (event.target.closest('.mc-select').getAttribute('id') != this.uniqueID) {
                this.open = false;
            }
        });
    },

    unmounted() {
        document.removeEventListener('mousedown', {});
        document.removeEventListener('click', {});
    },

    data() {
        return {
            optionSelected: {
                text: null,
                value: null,
            },
            open: false,
            uniqueID: (Math.floor(Math.random() * 9000) + 1000),
        };
    },

    methods: {
        toggleSelect() {
            this.open = !this.open
        },

        selectOption(event) {
            const options = this.$refs.options.children;       
            let optionText = event.target.text ?? event.target.textContent;
            let optionValue = event.target.value ?? event.target.getAttribute('value');
            let optionItem = event.target.outerHTML;

            if (this.optionSelected.value != optionValue) {
                for (const [index, option] of Object.entries(options)) {

                    if (this.hasGroups) {
                        for (const [_index, _option] of Object.entries(option.children)) {
                            if (_option.text == optionText || _option.textContent == optionText) {
                                this.optionSelected = {
                                    text: optionText,
                                    value: optionValue,
                                }

                                this.$emit('update:defaultValue', optionValue);
                                this.$refs.selected.innerHTML = optionItem;
                            }

                            _option.classList.remove('active');
                        }
                    } else {
                        if (option.text == optionText || option.textContent == optionText) {
                            this.optionSelected = {
                                text: optionText,
                                value: optionValue,
                            }
    
                            this.$emit('update:defaultValue', optionValue);
                            this.$refs.selected.innerHTML = optionItem;
                        }   

                        option.classList.remove('active');
                    }
                };

                this.$emit("changeOption", this.optionSelected);
            }

            event.target.classList.add('active');
            this.toggleSelect();
        },

        setDefaultOption(option) {
            if (this.defaultValue != null || this.defaultValue != '') {
                let optionText = option.text ?? option.textContent;
                let optionValue = option.value ?? option.getAttribute('value');
                let optionItem = option.outerHTML;
                
                if (optionValue == this.defaultValue) {
                    this.optionSelected = {
                        text: optionText,
                        value: optionValue,
                    }
                    
                    option.classList.add('active');
                    
                    this.$refs.selected.innerHTML = optionItem;
                }
            }
        }
    },
});