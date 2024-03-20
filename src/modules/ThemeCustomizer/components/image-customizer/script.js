app.component('image-customizer', {
    template: $TEMPLATES['image-customizer'],

    data() {
        const subsite = $MAPAS.subsite;

        return {
            subsite
        }
    },
});
