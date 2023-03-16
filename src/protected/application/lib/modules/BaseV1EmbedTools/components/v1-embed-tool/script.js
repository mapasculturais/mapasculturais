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
        window.addEventListener("message", this.listener, false);
    },

    unmounted() {
        window.removeEventListener("message", this.listener);
    },

    data() {
        const self = this;

        return {
            iframeHeight: this.height,
            listener: function(event) {            
                if (event.data.type == "resize") {
                    self.iframeHeight = event.data.data.height + 'px';
                }
    
                if (event.data.type == "message") {
                    const messages = useMessages();
                    const type = event.data.data.type;
                    const message = event.data.data.message;
                    
                    messages[type](message);
                }
            }
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
