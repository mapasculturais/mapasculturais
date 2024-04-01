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

        options: {
            type: Array,
        }
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
                const refOptions = this.$refs.options;
                const refSelected = this.$refs.selected;
                
                while (!option.hasAttribute('value') && option != refOptions) {
                    option = option.parentElement;
                }
    
                if (!option.hasAttribute('value')) {
                    console.error('Atributo value não encontrado');
                    return;
                }

                if (this.defaultValue != null || this.defaultValue != '') {
    
                    let optionText = option.text ?? option.textContent;
                    let optionValue = option.value ?? option.getAttribute('value');
                    let optionItem = option.outerHTML;
    
                    if (optionValue == this.defaultValue) {
                        this.optionSelected = {
                            text: optionText,
                            value: optionValue,
                        }
    
                        refSelected.innerHTML = optionItem;
                    }
                }
            }
    
            if (this.defaultValue === null || this.defaultValue === '' || this.$refs.selected.innerHTML === '') {
                this.$refs.selected.innerHTML = this.placeholder;
            }
        });

        document.addEventListener('mousedown', (event) => {
            const select = event.target.closest('.mc-select') || event.target.closest('.mc-select__options');

            if (!select) {
                this.open = false
            } else if (select.getAttribute('id') != this.uniqueID) {
                this.open = false;
            }
        });
    },

    unmounted() {
        document.removeEventListener('mousedown', {});
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

    computed: {
        selectOptions() {
            const result = [];

            for(let option of this.options) {
                if (typeof option == "string") {
                    result.push({
                        value: option,
                        label: option,
                    });
                } else {
                    result.push(option);
                }
            }

            return result;
        }
    },

    methods: {
        toggleSelect() {
            this.open = !this.open

            const refOptions = this.$refs.options;
            const refSelected = this.$refs.selected;

            refOptions.style.minWidth = refSelected.clientWidth + 'px'; 
        },

        selectOption(event) {
            const refOptions = this.$refs.options;
            const refSelected = this.$refs.selected;

            let option = event.target;

            while (!option.hasAttribute('value') && option != refOptions) {
                option = option.parentElement;
            }
            
            if (!option.hasAttribute('value')) {
                console.error('Atributo value não encontrado');
                return;
            }

            let optionText = option.text ?? option.textContent;
            let optionValue = option.value ?? option.getAttribute('value');
            let optionItem = option.outerHTML;
            
            this.optionSelected = {
                text: optionText,
                value: optionValue,
            }

            refSelected.innerHTML = optionItem;
            this.$emit('changeOption', this.optionSelected);
            this.$emit('update:defaultValue', optionValue);

            this.toggleSelect();
        }
    },
});