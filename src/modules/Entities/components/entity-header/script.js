app.component('entity-header', {
    template: $TEMPLATES['entity-header'],
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('entity-header')
        return { text }
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
    computed: {
        titleEdit () {
            switch(this.entity.__objectType) {
                case 'agent':
                    return (this.entity.type?.id == 1) ?  __('title agent-1', 'entity-header') : __('title agent-2', 'entity-header');
                case 'project':
                    return __('title project', 'entity-header');
                case 'space':
                    return __('title space', 'entity-header');
                case 'opportunity':
                    return __('title opportunity', 'entity-header');
                case 'event':
                    return __('title event', 'entity-header');
                case 'seal':
                    return __('title seal', 'entity-header');
            }
        }
    },
    methods: {
        url (source) {
            return `url(${source})`
        },
        buildSocialMediaLink(socialMedia){
            return Utils.buildSocialMediaLink(this.entity, socialMedia);
        }
    },
})
