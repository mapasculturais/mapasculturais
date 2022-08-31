app.component('mc-multiselect', {
    template: $TEMPLATES['mc-multiselect'],
    emits: ['open', 'close'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-multiselect')
        return { text }
    },

    created() {
        this.model.filter = '';
    },

    props: {
        editable: {
            type: Boolean,
            default: false,
        },

        items: {
            type: Array,
            required: true,
        },

        model: {
            type: Array,
            required: true,
        },

        classes: {
            type: String,
            required: false,
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

    computed: {
        filteredItems() {
            return this.items.filter((item) => {
                const _filter = this.model.filter.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                const _item = item.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

                if(_item.indexOf(_filter) >= 0) {
                    return item;
                }
            })
        }
        
    },

    methods: {
        highlightedItem(item) {
            const _filter = this.model.filter.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const _item = item.toLocaleUpperCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const indexOf = _item.indexOf(_filter);
            if(indexOf >= 0) {
                const part0 = item.substr(0, indexOf); 
                const part1 = item.substr(indexOf, this.model.filter.length); 
                const part2 = item.substr(indexOf + this.model.filter.length);
                return `${part0}<b><u>${part1}</u></b>${part2}`;
            } else {
                return item;
            }
        },

        remove(item) {
            const items = this.items;
            const indexOf = items.indexOf(item);
            items.splice(indexOf,1);
        },

        toggleItem(item, popover) {
            const items = this.model;
            if (items.indexOf(item) >= 0) {
                this.remove(item);
            } else {
                items.push(item);
                if(this.closeOnSelect) {
                    popover.close();
                }
            }
        },
        
        open() {
            this.$emit('open', this);
        },

        close() {
            this.$emit('close', this);
            this.model.filter = '';
        }
    }
});
