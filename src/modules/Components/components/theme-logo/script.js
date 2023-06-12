app.component('theme-logo', {
    template: $TEMPLATES['theme-logo'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('theme-logo')
        return { text }
    },

    props: {
        href: {
            type: String,
            default: null
        },
        bg1: {
            type: String,
            default: null,
        },
        bg2: {
            type: String,
            default: null,
        },
        bg3: {
            type: String,
            default: null,
        },
        bg4: {
            type: String,
            default: null,
        },
    },

    data() {
        return {
            title: $MAPAS.config.logo.title,
            subtitle: $MAPAS.config.logo.subtitle,
            colors: $MAPAS.config.logo.colors,
            logoImg: $MAPAS.config.logo.image,
            hideLabel: $MAPAS.config.logo.hideLabel,
        }
    },

    created() {
        this.colors.bg1 = this.bg1 ?? this.colors[0];
        this.colors.bg2 = this.bg2 ?? this.colors[1];
        this.colors.bg3 = this.bg3 ?? this.colors[2] ?? colors[0];
        this.colors.bg4 = this.bg4 ?? this.colors[3] ?? colors[1];
    },
});
