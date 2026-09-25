app.component('occurrence-card', {
    template: $TEMPLATES['occurrence-card'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('occurrence-card')
        return { text }
    },

    data() {
        return {
            event: this.occurrence.event,
            space: this.occurrence.space,
        }
    },

    props: {
        occurrence: {
            type: Object,
            required: true
        },

        hideSpace: {
            type: Boolean,
            default: false
        }
    },

    computed: {
        isVirtual() {
            // Check occurrence type or if space is the virtual space (id=0)
            return this.occurrence.type === 'virtual' || (this.space && this.space.id === 0);
        },
        virtualLinks() {
            if (this.occurrence.metadata && this.occurrence.metadata.links) {
                return this.occurrence.metadata.links;
            }
            return [];
        },
        seals() {
            return (this.occurrence.event.seals.length > 0 ? this.occurrence.event.seals.slice(0, 2) : false);
        },
        seals() {
            return (this.occurrence.event.seals.length > 0 ? this.occurrence.event.seals.slice(0, 2) : false);
        },
        areas() {
            return (Array.isArray(this.occurrence.event.terms.area) ? this.occurrence.event.terms.area.join(", ") : false);
        },
        tags() {
            return (Array.isArray(this.occurrence.event.terms.tag) ? this.occurrence.event.terms.tag.join(", ") : false);
        },
        linguagens() {
            return (Array.isArray(this.occurrence.event.terms.linguagem) ? this.occurrence.event.terms.linguagem.join(", ") : false);
        }
    },
    
    methods: {
        formatPlatform(platform) {
            const map = {
                'youtube': 'YouTube',
                'tiktok': 'TikTok',
                'instagram': 'Instagram',
                'zoom': 'Zoom',
                'google-meet': 'Google Meet',
                'facebook': 'Facebook',
                'twitch': 'Twitch',
                'teams': 'Microsoft Teams',
                'outros': 'Link'
            };
            return map[platform] || 'Link';
        },
    },
});
