app.component('entity-header', {
    template: $TEMPLATES['entity-header'],
    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-header')
        return { text }
    },
    data() {
        return {
            icon: '',
            titleEdit: ''
        }
    },
    props: {
        editable: {
            type: Boolean,
            default: false
        },
        entity: {
            type: Entity,
            required: true
        }
    },
    created() {
        console.log(this.entity);
        switch(this.entity.__objectType) {
            case 'agent': 
                this.icon = "fa-solid:user-friends";
                this.titleEdit = __('title agent-1', 'entity-header');
                break;
            case 'project':
                this.icon = "ri:file-list-2-line";
                this.titleEdit = __('title project', 'entity-header');
                break;
            case 'space':
                this.icon = "clarity:building-line";
                this.titleEdit = __('title space', 'entity-header');
                break;
            case 'opportunity':
                this.icon = "icons8:idea";
                this.titleEdit = __('title opportunity', 'entity-header');
                break;
            case 'event':
                this.icon = "ant-design:calendar-twotone";
                this.titleEdit = __('title event', 'entity-header');
                break;
        }
    },
    methods: {
        url (source) {
            return `url(${source})`
        },
    },
})
