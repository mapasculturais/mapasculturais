app.component('mapas-breadcrumb', {
    template: $TEMPLATES['mapas-breadcrumb'],
    
    data() {
        return {
            list: $MAPAS.breadcramb,
            cover: $MAPAS.requestedEntity.files.header,
        }
    },
});
