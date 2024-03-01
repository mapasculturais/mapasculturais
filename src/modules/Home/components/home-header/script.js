app.component('home-header', {
    template: $TEMPLATES['home-header'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('home-header')
        return { text }
    },

    data() {
        return {
            background: $MAPAS.config.homeHeader.background,
            
            banner: $MAPAS.config.homeHeader.banner,
            bannerLink: $MAPAS.config.homeHeader.bannerLink,
            downloadableLink: $MAPAS.config.homeHeader.downloadableLink,

            thirdBanner: $MAPAS.config.homeHeader.thirdBanner,
            secondBanner: $MAPAS.config.homeHeader.secondBanner,
            secondBannerLink: $MAPAS.config.homeHeader.secondBannerLink,
            secondDownloadableLink: $MAPAS.config.homeHeader.secondDownloadableLink,
        }
    },
});
