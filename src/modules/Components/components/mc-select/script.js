app.component('mc-select', {
    template: $TEMPLATES['mc-select'],
    emits: ['changeOption'],

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
            let className = [
                'mc-select', 
                'mc-select__selected-option', 
                'mc-select__options', 
                'mc-select__options--groups'
            ];
            
            const targetClasses = Array.from(event.target.classList);
            const parentClasses = Array.from(event.target.parentElement.classList);
            const groupClasses = Array.from(event.target.parentElement.parentElement.classList);

            const targetMatch = className.some(classString => targetClasses.includes(classString));
            const parentMatch = className.some(classString => parentClasses.includes(classString));
            const groupMatch = className.some(classString => groupClasses.includes(classString));

            if (!targetMatch && !parentMatch && !groupMatch) {
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

            if (this.optionSelected.text != optionText) {
                for (const [index, option] of Object.entries(options)) {

                    if (this.hasGroups) {
                        for (const [_index, _option] of Object.entries(option.children)) {
                            if (_option.text == optionText || _option.textContent == optionText) {
                                this.optionSelected = {
                                    text: optionText,
                                    value: optionValue,
                                }
        
                                this.$refs.selected.innerHTML = optionItem;
                            }
                        }
                    } else {
                        if (option.text == optionText || option.textContent == optionText) {
                            this.optionSelected = {
                                text: optionText,
                                value: optionValue,
                            }
    
                            this.$refs.selected.innerHTML = optionItem;
                        }   
                    }
                };

                this.$emit("changeOption", this.optionSelected);
            }
            
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
                    
                    this.$refs.selected.innerHTML = optionItem;
                }
            }
        }
    },
});