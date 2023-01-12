app.component('content-share' , {
    template: $TEMPLATES['content-share'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('content-share')
        return { text }
    },
    data() {
        return {
            entity: null,
            fields: [],
            modalTitle: this.text('title')
        }

    },
    computed: {
        socialNetworks () {
            const agent = $MAPAS.requestedEntity?.agent
            return {
                instagram: 'http://instagram/' + agent.instagram,
                twitter: agent.twitter,
                whatsapp: agent.whatsapp,
                telegram: agent.telegram
            }
        },
    }
});
