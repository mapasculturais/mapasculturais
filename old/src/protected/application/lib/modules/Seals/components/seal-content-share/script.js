app.component('seal-content-share' , {
    template: $TEMPLATES['seal-content-share'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('seal-content-share')
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
