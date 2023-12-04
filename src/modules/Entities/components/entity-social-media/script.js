app.component('entity-social-media', {
    template: $TEMPLATES['entity-social-media'],

    data() {
        return {
            show: !!(this.entity.instagram || this.entity.twitter || this.entity.vimeo || this.entity.linkedin || this.entity.facebook || this.entity.youtube || this.entity.spotify || this.entity.pinterest),
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: false
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },
    methods: {
        getPlaceHolders(){
            console.log($DESCRIPITIONS);
        },
        buildSocialMediaLink(socialMedia){
            return Utils.buildSocialMediaLink(this.entity, socialMedia);
        }
    },
});
