app.component('home-customizer', {
    template: $TEMPLATES['home-customizer'],
    
    created() { 
        if(!this.subsite.homeConfigurations){
            this.subsite.homeConfigurations = {};
            for(let section of this.homeConfigurations){
                for(let text of section.texts){
                    this.subsite.homeConfigurations[text.slug] = '';
                }
            }
        }
    },

    data() {
        let subsite = $MAPAS.subsite;
        return {
            subsite
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

    methods: {
        save(entity) {
            entity.save();
        }
    }
});
