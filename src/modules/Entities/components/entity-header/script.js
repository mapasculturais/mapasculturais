app.component('entity-header', {
    template: $TEMPLATES['entity-header'],
    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-header')
        return { text }
    },
    data() {
        return {
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
        switch(this.entity.__objectType) {
            case 'agent': 
                this.titleEdit = (this.entity.type?.id == 1) ?  __('title agent-1', 'entity-header') : __('title agent-2', 'entity-header');
                break;
            case 'project':
                this.titleEdit = __('title project', 'entity-header');
                break;
            case 'space':
                this.titleEdit = __('title space', 'entity-header');
                break;
            case 'opportunity':
                this.titleEdit = __('title opportunity', 'entity-header');
                break;
            case 'event':
                this.titleEdit = __('title event', 'entity-header');
                break;
            case 'seal':
                this.titleEdit = __('title seal', 'entity-header');
                break;
        }
    },
    computed: {
        hasSocialNetworks() {
            const socialNetworks = [
                'twitter',
                'linkedin',
                'youtube',
                'vimeo',
                'spotify',
                'facebook',
                'instagram',
                'telegram',
                'pinterest',
                'whatsapp',
                'tiktok',
            ]

            let itHas = false;
            socialNetworks.forEach(network => {
                if (this.entity[network]) {
                    itHas = true;
                }
            });

            return itHas;
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
