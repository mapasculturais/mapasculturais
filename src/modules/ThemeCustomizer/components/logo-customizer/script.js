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
            const originals =
                ($MAPAS.config.logoCustomizer && $MAPAS.config.logoCustomizer.originalColors) ||
                this.default_colors ||
                [];
            if (!!!this.subsite.custom_colors) {
                return {
                    first: originals[0],
                    second: originals[1],
                    third: originals[2],
                    fourth: originals[3],
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
