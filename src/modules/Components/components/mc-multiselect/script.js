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
    },

    data() {
        let dataItems = {};        
        if (Array.isArray(this.items)) {
            for (let item of this.items) {
                dataItems[item] = item;
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
                const label = this.dataItems[value];
                const _filter = this.filter.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                const _item = label.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

                if(_item.indexOf(_filter) >= 0) {
                    result.push({value, label});
                }
            }

            return result.sort((a,b) => {
                if (a.label > b.label) {
                    return 1;
                } else if (a.label < b.label) {
                    return -1;
                } else {
                    return 0;
                }
            });
        },
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
            if (this.model.indexOf(key) >= 0) {
                this.remove(key);
            } else {
                this.model.push(key);
                this.$emit('selected', key);

            }
        },       

        open() {
            this.$emit('open', this);
        },
        
        close() {
            this.$emit('close', this);
            this.filter = '';
        },

        setFilter(text) {
            this.filter = text;
        }
    }
});
