app.component('space-info', {
    template: $TEMPLATES['space-info'],
    emits: [],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('space-info')
        return { text }
    },
    
    data() {
        return {
            
        };
    },

    props: {
        
        entity: {
            type: Entity,
            required: true
        },
    },
});
