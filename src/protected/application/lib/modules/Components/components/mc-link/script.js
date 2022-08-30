app.component('mc-link', {
    template: $TEMPLATES['mc-link'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-link')
        return { text }
    },

    props: {
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
            type: String,
            default: ''
        }
    },

    data() {
        let url = '';
        let classes = this.class;
        let queryString = this.serializeQueryString();
        queryString = queryString ? `?${queryString}` : queryString;

        if (this.entity) {
            url = this.entity.getUrl(this.route, this.urlParams) + queryString;
            classes += ` ${this.entity.__objectType}__color` ;
        } else {
            const parts = this.route.split('/');
            url = Utils.createUrl(parts[0], parts[1], this.urlParams)  + queryString;
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
