app.component('logo-customizer', {
    template: $TEMPLATES['logo-customizer'],

    data() {
        const subsite = $MAPAS.subsite;
        const default_colors = $MAPAS.config.logo.colors;
        return {
            subsite,
            default_colors,
        }
    },

    computed: {
        colors() {
            console.log($MAPAS.config.logoCustomizer)
            if (!!!this.subsite.custom_colors) {
                return {
                    first: $MAPAS.config.logoCustomizer.originalColors[0],
                    second: $MAPAS.config.logoCustomizer.originalColors[1],
                    third: $MAPAS.config.logoCustomizer.originalColors[2],
                    fourth: $MAPAS.config.logoCustomizer.originalColors[3],
                }
            } else {
                return {
                    first: this.default_colors[0],
                    second: this.default_colors[1],
                    third: this.default_colors[2],
                    fourth: this.default_colors[3],
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
