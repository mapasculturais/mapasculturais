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
            default: null,
        },
        title: {
            type: String,
            default: null,
        },
        subtitle: {
            type: String,
            default: null,
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
        logoImg: {
            type: String,
            default: null,
        },
        hideLabel: {
            type: Boolean,
            default: null,
        },
    },

    data() {
        return {
            colors: $MAPAS.config.logo.colors,
            defaultLogoImg: $MAPAS.config.logo.image,
            defaultHideLabel: $MAPAS.config.logo.hideLabel,
        }
    },

    computed: {
        logo_title() {
            return this.title ?? $MAPAS.config.logo.title;
        },
        
        logo_subtitle() {
            return  this.subtitle ?? $MAPAS.config.logo.subtitle;
        },

        first_color() {
            return this.bg1 ?? this.colors[0];
        },

        second_color() {
            return this.bg2 ?? this.colors[1];
        },

        third_color() {
            return this.bg3 ?? this.colors[2] ?? colors[0];
        },

        fourth_color() {
            return this.bg4 ?? this.colors[3] ?? colors[1];
        },

        logo_image() {
            return this.logoImg ?? this.defaultLogoImg;
        },

        logo_hide_label() {
            return this.hideLabel ?? this.defaultHideLabel;
        },
    },
});
