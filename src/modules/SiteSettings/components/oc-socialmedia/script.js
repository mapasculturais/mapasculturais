app.component('oc-socialmedia', {
    template: $TEMPLATES['oc-socialmedia'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    computed: {
        socialmediaList() {
            return [
                { label: 'Facebook', value: 'facebook', icon: 'one-click-facebook' },
                { label: 'Instagram', value: 'instagram', icon: 'one-click-instagram' },
                { label: 'Linkedin', value: 'linkedin', icon: 'one-click-linkedin' },
                { label: 'Pinterest', value: 'pinterest', icon: 'one-click-pinterest' },
                { label: 'Spotify', value: 'spotify', icon: 'one-click-spotify' },
                { label: 'Tiktok', value: 'tiktok', icon: 'one-click-tiktok' },
                { label: 'X', value: 'twitter', icon: 'one-click-x' },
                { label: 'Vimeo', value: 'vimeo', icon: 'one-click-vimeo' },
                { label: 'Youtube', value: 'youtube', icon: 'one-click-youtube' }
            ]
        },
        socialmediaLabels() {
            return {
                facebook: 'Facebook',
                instagram: 'Instagram',
                linkedin: 'Linkedin',
                pinterest: 'Pinterest',
                spotify: 'Spotify',
                tiktok: 'Tiktok',
                twitter: 'X "Twitter"',
                vimeo: 'Vimeo',
                youtube: 'Youtube'
            };
        }
        
    },
    data() {
        return {
            socialmedia: this.entity.socialmedia || [],
            socialmediaData: this.entity.socialmediaData || {}
        }
    },
    methods: {
        change(key) {
            this.socialmediaData = {};
            this.entity.socialmedia = this.socialmedia;
            let config = this.socialmediaList.find(item => {return item.value == key})


            for(item of this.socialmedia) {
                this.socialmediaData[item] = this.entity.socialmediaData?.[item] || {};
            }

            this.entity.socialmediaData = this.socialmediaData;
        },
        capitalizeFirstLetter(string) {
            if(string == "twitter") {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }
        }
    }
});
