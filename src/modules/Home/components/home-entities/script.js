app.component('home-entities', {
    template: $TEMPLATES['home-entities'],

    data() {
        const subsite = $MAPAS.subsite;

        return {
            subsite
        }
    },
});
