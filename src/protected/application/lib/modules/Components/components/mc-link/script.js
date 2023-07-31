app.component('mc-link', {
    template: $TEMPLATES['mc-link'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-link')
        return { text }
    },

    props: {
        id: String,
        
        entity: {
            type: Entity,
            required: false
        },

        label: {
            type: String,
            required: false
        },

        icon: {
            type: [Boolean, String],
            required: false
        },

        rightIcon: {
            type: Boolean,
            default: false
        },

        route: {
            type: String,
            default: 'single'
        },

        params: {
            type: [Array, Object],
            default: []
        },

        getParams: {
            type: Object,
            default: {}
        },

        class: {
            type: [String, Array, Object],
            default: ''
        },

        hash: {
            type: String,
            default: null
        }

    },

    data() {
        let url = '';
        let classes = this.class;
        let queryString = this.serializeQueryString();
        queryString = queryString ? `?${queryString}` : queryString;

        if (this.entity) {
            url = this.entity.getUrl(this.route, this.params) + queryString;
            classes += ` ${this.entity.__objectType}__color` ;
        } else {
            const parts = this.route.split('/');
            url = Utils.createUrl(parts[0], parts[1], this.params)  + queryString;
        }

        if (this.hash) {
            url = url+'#'+this.hash;
        }

        return {url, classes};
    },

    methods: {
        serializeQueryString() {
            const obj = this.getParams;
            const str = [];
            for (let p in obj){
                if (obj.hasOwnProperty(p)) {
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
            }
            return str.join("&");
        }
    }
});
