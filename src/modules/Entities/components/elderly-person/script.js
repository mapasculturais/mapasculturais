app.component('elderly-person', {
    template: $TEMPLATES['elderly-person'],
    emits: [],
    
    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    setup() {
        const text = Utils.getTexts('elderly-person')
        return { text }
    },
    mounted() {
        globalThis.addEventListener('afterFetch', (event) => {
            const response = event.detail;
        
            response.clone().json().then(data => {
                this.entity.idoso = data.idoso;
            }).catch(err => {
                console.error('Erro ao processar o response:', err);
            });
        });
    },
});