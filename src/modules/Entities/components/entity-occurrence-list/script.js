app.component('entity-occurrence-list', {
    template: $TEMPLATES['entity-occurrence-list'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-occurrence-list')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true,
        },
        editable: {
            type: Boolean,
            default: false
        },
        createEvent: {
            type: Boolean,
            default: false,
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    data() {
        
        return {
            showMap: false,
        }
    },

    methods: {
        toggleMap(event) {
            this.showMap = !this.showMap;
            // const occurrenceMap = event.target.parentElement.parentElement.nextSibling;
            // occurrenceMap.classList.toggle('showMap');
        },

        formatPrice(price) {
            let newPrice = price;
            if (/^\d+(?:\.\d+)?$/.test(newPrice)) {
	            return parseFloat(newPrice).toLocaleString('pt-BR', { style: 'currency', currency: __('currency', 'entity-occurrence-list')  });
            } else {
                return newPrice;
            }
        },

        addToOccurrenceList(occurrence) {
            /* const api = new API('eventoccurrence'); */
            const lists = useEntitiesLists();
            const occurrences = lists.fetch('occurrenceList', 'default');
            occurrences.push(occurrence);
        },

        async deleteOccurrence(occurrence) {
            try {
                await occurrence.delete(true);
                const lists = useEntitiesLists();
                const occurrences = lists.fetch('occurrenceList', 'default');
                const index = occurrences.findIndex((item) => item.id === occurrence.id);
                
                if (index !== -1) {
                    occurrences.splice(index, 1); 
                }
            } catch (error) {
                console.error(error);
            }
        }
    },
});
