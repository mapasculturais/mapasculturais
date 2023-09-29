app.component('home-header', {
    template: $TEMPLATES['home-header'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('home-header')
        return { text }
    },

    data() {
        return {
            title: $MAPAS.config.homeHeader.title,
            description: $MAPAS.config.homeHeader.description,
            background: $MAPAS.config.homeHeader.background,
            banner: $MAPAS.config.homeHeader.banner,
            bannerLink: $MAPAS.config.homeHeader.bannerLink,
            downloadableLink: $MAPAS.config.homeHeader.downloadableLink,
        }
    },
});
