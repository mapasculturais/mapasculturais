app.component('entity-header', {
    template: $TEMPLATES['entity-header'],
    data() {
        return {
            icon: ''
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
        switch(this.entity.__objectType) {
            case 'agent': 
                this.icon = "fa-solid:user-friends";
                break;
            case 'project':
                this.icon = "ri:file-list-2-line";
                break;
            case 'space':
                this.icon = "clarity:building-line";
                break;
            case 'opportunity':
                this.icon = "icons8:idea";
                break;
            case 'event':
                this.icon = "ant-design:calendar-twotone";
                break;
        }
    },
    methods: {
        url (source) {
            return `url(${source})`
        },
    },
})
