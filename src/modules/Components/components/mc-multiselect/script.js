app.component('mc-multiselect', {
    template: $TEMPLATES['mc-multiselect'],
    emits: ['open', 'close', 'selected', 'removed'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-multiselect')
        return { text }
    },

    props: {
        editable: {
            type: Boolean,
            default: false,
        },

        items: {
            type: [Array, Object],
            required: true,
        },

        model: {
            type: Array,
            required: true,
        },

        title: {
            type: String,
        },

        closeOnSelect: {
            type: Boolean,
            default: true
        },

        hideFilter: {
            type: Boolean,
            default: false
        },

        hideButton: {
            type: Boolean,
            default: false
        },

        maxOptions: {
            type: Number,
            default: 0,
        },

        disabled: {
            type: Boolean,
            default: false,
        },
        
        preserveOrder: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        let dataItems = {};        
        if (Array.isArray(this.items)) {
            for (let item of this.items) {
                if(typeof item == 'object') {
                    dataItems[item.value] = item
                } else {
                    dataItems[item] = item;
                }
            }
        } else {
            dataItems = Object.assign({}, this.items);
        }
        return { dataItems, filter: '' };
    },

    computed: {
        filteredItems() {
            const result = [];
            for (let value in this.dataItems) {
                let label = typeof this.dataItems[value] == 'object' ? this.dataItems[value].label : this.dataItems[value];
                const _filter = this.filter.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                const _item = label.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

                if(_item.indexOf(_filter) >= 0) {
                    result.push({value, label});
                }
            }

            if(this.preserveOrder) {
                return result;
            } else {
                return result.sort((a,b) => {
                    if (a.label > b.label) {
                        return 1;
                    } else if (a.label < b.label) {
                        return -1;
                    } else {
                        return 0;
                    }
                });
            }
        },

        canSelectMore() {
            return this.maxOptions === null || this.maxOptions === 0 || this.model.length < this.maxOptions;
        }
    },

    methods: {
        highlightedItem(item) {
            const _filter = this.filter.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const _item = item.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const indexOf = _item.indexOf(_filter);
            if(indexOf >= 0) {
                const part0 = item.substr(0, indexOf); 
                const part1 = item.substr(indexOf, this.filter.length); 
                const part2 = item.substr(indexOf + this.filter.length);
                return `${part0}<b><u>${part1}</u></b>${part2}`;
            } else {
                return item;
            }
        },

        remove(key) {
            const indexOf = this.model.indexOf(key);
            this.model.splice(indexOf,1);
            this.$emit('removed', key);

        },

        toggleItem(key) {
            if(key == '@NA') {
                if (this.model.includes(key)) {
                    this.remove(key);
                } else {
                    while (this.model.length > 0) {
                        this.remove(this.model[0]);
                    }
                    
                    this.model.push(key);
                    this.$emit('selected', key);
                }
            } else {
                const ndIndex = this.model.indexOf('@NA');
                
                if (ndIndex >= 0) {
                    this.model.splice(ndIndex, 1);
                }

                if (this.model.indexOf(key) >= 0) {
                    this.remove(key);
                } else if(this.canSelectMore) {
                    this.model.push(key);
                    this.$emit('selected', key);
                }
            }
        },       

        open() {
            this.$emit('open', this);
        },
        
        close(popover) {
            this.$emit('close', this);
            this.filter = '';
            popover.close();
        },

        setFilter(text) {
            this.filter = text;
        }
    }
});
