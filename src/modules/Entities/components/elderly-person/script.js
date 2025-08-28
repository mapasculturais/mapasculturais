app.component('elderly-person', {
    template: $TEMPLATES['elderly-person'],
    emits: [],
    
    props: {
        entity: {
            type: Entity,
            required: true
        },
    },
    computed: {
        idoso() {
            return this.entity?.idoso ? this.entity.idoso : false;
        }
    },
    setup() {
        const text = Utils.getTexts('elderly-person')
        return { text }
    },
    mounted() {
        globalThis.addEventListener('afterFetch', (event) => {
            const response = event.detail;
        
            response.clone().json().then(data => {
                this.entity.idoso = data.idoso ?? this.idoso;
            }).catch(err => {
                console.error('Erro ao processar o response:', err);
            });
        });
    },
});