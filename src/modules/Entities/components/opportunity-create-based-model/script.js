app.component('opportunity-create-based-model', {
    template: $TEMPLATES['opportunity-create-based-model'],
    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('opportunity-create-based-model')
        return { text, messages }
    },
    props: {
        entity: {
            type: Entity,
            required: true,
        },
    },

    data() {
        let sendSuccess = false;
        let formData = {
            name: this.entity.name
        }

        return { sendSuccess, formData }
    },

    methods: {
        async save() {
            const api = new API(this.entity.__objectType);

            let objt = this.formData;
            objt.entityId = this.entity.id;
            
            let error = null;

            if (error = this.validade(objt)) {
                let mess = "";
                mess = this.text('Todos os campos são obrigatorio');
                this.messages.error(mess);
                return;
            }

            await api.POST(`/opportunity/generateopportunity/${objt.entityId}`, objt).then(response => response.json().then(dataReturn => {
                this.messages.success(this.text('Aguarde, estamos gerando a oportunidade baseada no modelo.'));

                this.sendSuccess = true;

                setTimeout(() => {
                    window.location.href = `/gestao-de-oportunidade/${dataReturn.id}/#info`;
                }, "5000");                
            }));
        },
        validade(objt) {
            let result = null;
            let ignore = [];

            Object.keys(objt).forEach(function (item) {
                if (!objt[item] && !ignore.includes(item)) {
                    result = item;
                    return;
                }
            });
            return result;
        },
    },
});
