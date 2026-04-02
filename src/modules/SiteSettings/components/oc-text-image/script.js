app.component('oc-text-image', {
    template: $TEMPLATES['oc-text-image'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        slug: {
            type: String,
            required: ''
        },
        tabsActive: {
            type: [Boolean, Object],
            required: false
        },
    },
    computed: {},
    data() {
        let tabGroups = this.tabsActive || this.tabsDefault()

        return {
            tabGroups
        }
    },
    methods: {
        tabsDefault() {
            return {
                tabs: [
                    { label: 'Texto', isActive: true, submenu: [], ref: 'text', useActions: true },
                    { label: 'Imagem', isActive: false, submenu: [], ref: 'image', useActions: false },
                ],
            };
        }
    }
});
