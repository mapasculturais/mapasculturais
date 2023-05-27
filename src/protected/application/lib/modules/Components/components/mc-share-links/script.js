app.component('mc-share-links', {
    template: $TEMPLATES['mc-share-links'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    props: {
        title: {
            type: String,
            default: 'Compartilhar'
        },
        text: {
            type: String,
            default: ''
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    methods: {
        click( action ) {
            switch(action){
                case 'twitter':
                    window.open('https://twitter.com/intent/tweet?text=' + this.text + ' - ' + encodeURIComponent(document.URL)); 
                    break;
                case 'facebook':
                    window.open('http://www.facebook.com/share.php?href=' + encodeURIComponent(document.URL));
                    break;
                case 'whatsapp':
                    window.open('https://api.whatsapp.com/send?text=' + this.text + ' - ' + encodeURIComponent(document.URL));
                    break;
                case 'whatsapp-mobile':
                    window.open('whatsapp://send?text=' + this.text + ' - ' + encodeURIComponent(document.URL));
                    break;
                case 'telegram':
                    window.open('https://telegram.me/share/url?url=' + this.text + ' - ' + encodeURIComponent(document.URL));
                    break;
            }
            return false;
        }
    },
});
