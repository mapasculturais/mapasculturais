app.component('logo-customizer', {
    template: $TEMPLATES['logo-customizer'],

    data() {
        let subsite = $MAPAS.subsite;
        let default_colors = $MAPAS.config.logo.colors;

        return {
            subsite,
            default_colors,
        }
    },

    computed: {
        colors() {
            if (!!!this.subsite.custom_colors) {
                return {
                    first: $MAPAS.config.logoCustomizer.originalColors[0],
                    second: $MAPAS.config.logoCustomizer.originalColors[1],
                    third: $MAPAS.config.logoCustomizer.originalColors[2],
                    fourth: $MAPAS.config.logoCustomizer.originalColors[3],
                }
            } else {
                return {
                    first: this.subsite.logo_color1,
                    second: this.subsite.logo_color2,
                    third: this.subsite.logo_color3,
                    fourth: this.subsite.logo_color4,
                }
            }
        },

        title() {
            return this.subsite.logo_title;
        },

        subtitle() {
            return this.subsite.logo_subtitle;
        },
    },
});
