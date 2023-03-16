app.component('v1-embed-tool', {
    template: $TEMPLATES['v1-embed-tool'],

    props: {
        route: {
            type: String,
            required: true,
        },
        id: {
            type: String,
            default: null,
        },
        hash: {
            type: String,
            default: null,
        },
        maxHeight: {
            type: String,
        },
        minHeight: {
            type: String,
        },
        height: {
            type: String,
        },
        iframeId: {
            type: String
        }
    },

    created() {
        const self = this;
        window.addEventListener("message", function(event) {            
            if (event.data.type == "resize") {
                self.iframeHeight = event.data.data.height + 'px';
            }

            if (event.data.type == "message") {
                const messages = useMessages();
                const type = event.data.data.type;
                const message = event.data.data.message;
                
                messages[type](message);
            }
        }, false);
    },

    unmounted() {
        window.removeEventListener("message");
    },

    data() {
        return {
            iframeHeight: this.height,
        }
    },

    computed: {
        url() {
            let url = $MAPAS.baseURL + 'embedtools/' + this.route;

            if (this.id) {
                url += '/' + this.id;
            }

            if (this.hash) {
                url += '#' + this.hash;
            }

            console.log(url);
            return url;
        }
    },
});
