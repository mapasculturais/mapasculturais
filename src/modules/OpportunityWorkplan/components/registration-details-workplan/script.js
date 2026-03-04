app.component('registration-details-workplan', {
    template: $TEMPLATES['registration-details-workplan'],
    setup() {
        const text = Utils.getTexts('registration-details-workplan');
        return { text };
    },
    props: {
        registration: {
            type: Entity,
            required: true
        },
    },
    data() {
        this.getWorkplan();

        const entityWorkplan = new Entity('workplan');

        return {
            opportunity: this.registration.opportunity,
            workplan: entityWorkplan,
        };
    },
    computed: {
        getWorkplanLabelDefault() {
            return this.opportunity.workplanLabelDefault ? this.opportunity.workplanLabelDefault : $MAPAS.EntitiesDescription.opportunity.workplanLabelDefault.default_value;
        },
        getGoalLabelDefault() {
            const label = this.opportunity.goalLabelDefault ? this.opportunity.goalLabelDefault : $MAPAS.EntitiesDescription.opportunity.goalLabelDefault.default_value;
            return this.pluralParaSingular(label);
        },
        getDeliveryLabelDefault() {
            const label = this.opportunity.deliveryLabelDefault ? this.opportunity.deliveryLabelDefault : $MAPAS.EntitiesDescription.opportunity.deliveryLabelDefault.default_value;
            return this.pluralParaSingular(label);
        },
    },
    methods: {
        getWorkplan() {
            const api = new API('workplan');
            
            const response = api.GET(`${this.registration.id}`);
            response.then((res) => res.json().then((data) => {
                if (data.workplan != null) {
                    this.workplan = data.workplan;
                }
            }));
        },
        convertToCurrency(field) {
            return new Intl.NumberFormat("pt-BR", {
                style: "currency",
                currency: "BRL"
              }).format(field);
        },
        pluralParaSingular(texto) {
            const palavras = texto.split(' ');
        
            const palavrasNoSingular = palavras.map(palavra => {
                if (palavra.endsWith('ões')) {
                    palavra = palavra.slice(0, -3) + 'ão';
                } else if (palavra.endsWith('ães')) {
                    palavra = palavra.slice(0, -3) + 'ão';
                } else if (palavra.endsWith('ais')) {
                    palavra = palavra.slice(0, -2) + 'al';
                } else if (palavra.endsWith('éis')) {
                    palavra = palavra.slice(0, -2) + 'el';
                } else if (palavra.endsWith('óis')) {
                    palavra = palavra.slice(0, -2) + 'ol';
                } else if (palavra.endsWith('uis')) {
                    palavra = palavra.slice(0, -2) + 'ul';
                } else if (palavra.endsWith('is')) {
                    palavra = palavra.slice(0, -2) + 'il';
                } else if (palavra.endsWith('ns')) {
                    palavra = palavra.slice(0, -2) + 'm';
                } else if (palavra.endsWith('s')) {
                    palavra = palavra.slice(0, -1);
                }
        
                return palavra.toLowerCase();
            });
    
            return palavrasNoSingular.join(' ');
        }
    },
})