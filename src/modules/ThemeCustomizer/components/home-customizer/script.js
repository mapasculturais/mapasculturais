app.component('home-customizer', {
    template: $TEMPLATES['home-customizer'],
    
    props: {
        subsite: {
            type: Entity,
            required: true,
        },
    },

    created() { 
        if(!this.subsite.homeTexts){
            this.subsite.homeTexts = {};
            for(let section of this.homeConfigurations){
                for(let text of section.texts){
                    this.subsite.homeTexts[text.slug] = '';
                }
            }
        }
    },

    computed: {
        homeConfigurations() {
            const configs = $MAPAS.config.homeConfigurations;
            
            const configWithImage = configs.filter(section => section.hasOwnProperty('image'));
            const configWithoutImage = configs.filter(section => !section.hasOwnProperty('image'));
            const reorganizedConfig = configWithImage.concat(configWithoutImage);

            return reorganizedConfig;
        }
    },
});
