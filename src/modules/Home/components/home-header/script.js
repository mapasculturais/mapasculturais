app.component('home-header', {
    template: $TEMPLATES['home-header'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('home-header')
        return { text }
    },

    data() {
        return {
            subsite: $MAPAS.subsite ?? {},
            
            banner: $MAPAS.config.homeHeader.banner,
            bannerLink: $MAPAS.config.homeHeader.bannerLink,
            downloadableLink: $MAPAS.config.homeHeader.downloadableLink,

            secondBanner: $MAPAS.config.homeHeader.secondBanner,
            secondBannerLink: $MAPAS.config.homeHeader.secondBannerLink,
            secondDownloadableLink: $MAPAS.config.homeHeader.secondDownloadableLink,

            thirdBanner: $MAPAS.config.homeHeader.thirdBanner,
            thirdBannerLink: $MAPAS.config.homeHeader.thirdBannerLink,
            thirdDownloadableLink: $MAPAS.config.homeHeader.thirdDownloadableLink,
        }
    },
    computed: {
        background(){
            if(this.subsite?.files?.header){
                return this.subsite.files.header.url;
            }
            return $MAPAS.config.homeHeader.background;
        }
    },
});
