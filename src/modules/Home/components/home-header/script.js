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
            bannerAlt: $MAPAS.config.homeHeader.bannerAlt,
            bannerOpenInNewTab: $MAPAS.config.homeHeader.bannerOpenInNewTab,

            secondBanner: $MAPAS.config.homeHeader.secondBanner,
            secondBannerLink: $MAPAS.config.homeHeader.secondBannerLink,
            secondBannerAlt: $MAPAS.config.homeHeader.secondBannerAlt,
            secondBannerOpenInNewTab: $MAPAS.config.homeHeader.secondBannerOpenInNewTab,

            thirdBanner: $MAPAS.config.homeHeader.thirdBanner,
            thirdBannerLink: $MAPAS.config.homeHeader.thirdBannerLink,
            thirdBannerAlt: $MAPAS.config.homeHeader.thirdBannerAlt,
            thirdBannerOpenInNewTab: $MAPAS.config.homeHeader.thirdBannerOpenInNewTab,
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
