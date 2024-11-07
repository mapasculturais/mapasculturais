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

        showFilter: {
            type: Boolean,
            default: false,
        },

        small: {
            type: Boolean,
            default: false,
        },

        options: {
            type: Array,
            default: []
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    watch: {
        defaultValue(newValue, oldValue) {
            setTimeout(() => {
                const options = this.$refs.options.children;
                this.defaultOptions = Object.freeze(Array.from(options));
                
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
    
                    if (newValue != null || newValue != '') {
                        
        
                        let optionText = option.text ?? option.textContent;
                        let optionValue = option.value ?? option.getAttribute('value');
                        let optionItem = option.outerHTML;
        
                        if (optionValue == newValue) {
                            this.optionSelected = {
                                text: optionText,
                                value: optionValue,
                            }
        
                            refSelected.innerHTML = optionItem;
                        }
                    }
                }
        
                if (newValue === null || newValue === '' || this.$refs.selected.innerHTML === '') {
                    this.$refs.selected.innerHTML = this.placeholder;
                }
            });
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
            const options = this.$refs.options.querySelectorAll('[value]');
            
            this.defaultOptions = Object.freeze(Array.from(options));
            
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
                } else {
                    this.setMatchingOption(option);
                }
            }
    
            if (this.defaultValue === null || this.defaultValue === '' || this.$refs.selected.innerHTML === '') {
                this.$refs.selected.innerHTML = this.placeholder;
            }
        });

        document.addEventListener('mousedown', (event) => {
            const select = event.target.closest('.mc-select') || event.target.closest('.mc-select__options');
            
            if (!event.target.closest('.mc-select__filter')) {
                if (!select) {
                    this.open = false
                } else if (select.getAttribute('id') != this.uniqueID) {
                    this.open = false;
                }
            }
        });

        document.addEventListener('keydown', (e) => {
            if((e.key=="27") || (e.key =="Escape")) {
                this.closeSelect();
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
            filter: '',
            defaultOptions: [],
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
        focus() {
            const inputs = this.$refs.selected.getElementsByTagName('input');
            if (inputs.length) {
                setTimeout(() => {
                    if (inputs[0].getAttribute("type") == 'text') {
                        inputs[0].focus();
                    }
                }, 100);
            }
        },

        openSelect() {
            this.open = true;
        },

        closeSelect() {
            this.open = false;
            this.filter = '';
            this.filterOptions();
        },

        toggleSelect() {
            this.open ? this.closeSelect() : this.openSelect();

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

            refSelected.innerHTML = optionItem.replace(/<b><u>(.*?)<\/u><\/b>/, '$1');
            this.$emit('changeOption', this.optionSelected);
            this.$emit('update:defaultValue', optionValue);

            this.toggleSelect();
        },

        filterOptions() {
            let options = this.defaultOptions;
            let result = [];
            
            for (const [index, option] of Object.entries(options)) {
                const label = option.text ?? option.textContent;
                const _filter = this.filter.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                const _item = label.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

                const indexOf = _item.indexOf(_filter);
                if(indexOf >= 0) {
                    const part0 = label.substr(0, indexOf); 
                    const part1 = label.substr(indexOf, this.filter.length); 
                    const part2 = label.substr(indexOf + this.filter.length);
                    
                    let highlighted = `${part0}<b><u>${part1}</u></b>${part2}`;

                    const cleanText = option.innerHTML.replace(/<b><u>(.*?)<\/u><\/b>/, '$1');

                    option.innerHTML = cleanText.replace(label, highlighted);
                    result.push(option);
                }
            }

            this.$refs.options.innerHTML = '';

            result = result.sort((a, b) => {
                let aText = a.text || a.textContent;
                let bText = b.text || b.textContent;
                if (aText > bText) {
                    return 1;
                } else if (aText < bText) {
                    return -1;
                } else {
                    return 0;
                }
            });

            if (result.length > 0) {
                result.forEach(option => {
                    this.$refs.options.appendChild(option);
                });
            } else {
                this.$refs.options.innerHTML = 'Nenhuma opção encontrada';
            }
        },

        setMatchingOption(option) {
            const refSelected = this.$refs.selected;
            
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
    },
});