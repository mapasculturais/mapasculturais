app.component('home-texts', {
    template: $TEMPLATES['home-texts'],
    
    created() { 
        if(!this.subsite.homeTexts){
            this.subsite.homeTexts = {};
            for(let section of this.homeTexts){
                for(let text of section.texts){
                    this.subsite.homeTexts[text.slug] = '';
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
        homeTexts() {
            return $MAPAS.config.homeTexts
        }
    },
    methods: {
        save(entity) {
            entity.save();
        }
    }
});
