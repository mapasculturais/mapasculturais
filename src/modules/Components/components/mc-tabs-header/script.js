app.component('mc-tabs-header', {
    template: $TEMPLATES['mc-tabs-header'],

    props: {
        list: {
            type: Array,
            default: null,
        },
        slugProp: {
            type: String,
            default: 'slug',
        },
        tabs: {
            type: Array,
            required: true,
        },
    },

    emits: ['sort'],

    methods: {
        reorderTabs (newList) {
            const newIndexes = {}
            newList.forEach((tab, index) => {
                newIndexes[tab[this.slugProp]] = index
            })

            const newTabs = this.tabs.toSorted((a, b) => newIndexes[a.slug] - newIndexes[b.slug])

            this.$emit('sort', { list: newList, tabs: newTabs });
        },
    },
});
