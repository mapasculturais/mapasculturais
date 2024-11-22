app.component('complaint-suggestion', {
    template: $TEMPLATES['complaint-suggestion'],
    components: {
        VueRecaptcha
    },
    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('complaint-suggestion')
        return { text, messages }
    },
    props: {
        entity: {
            type: Entity,
            required: true,
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },

    },

    data() {
        let isAuth = $MAPAS.complaintSuggestionConfig.isAuth;
        let typeMessage = "";
        let hasCaptcha = false;
        let definitions = $MAPAS.notification_type;
        let recaptchaResponse = '';
        let formData = {
            name: $MAPAS.complaintSuggestionConfig.senderName,
            email: $MAPAS.complaintSuggestionConfig.email,
            type: "",
            message: "",
            anonimous: false,
            copy: false,
        }

        let options = {
            complaint: definitions.compliant_type.config.options,
            suggestion: definitions.suggestion_type.config.options,
        }

        return { definitions, options, typeMessage, hasCaptcha, recaptchaResponse, formData, isAuth }
    },

    methods: {
        async send() {

            const api = new API(this.entity.__objectType);
            let url = api.createUrl(this.typeMessage);

            let objt = this.formData;
            objt.entityId = this.entity.id;
            
            // A flag será usada para identificar se o componente filho implementa o Captcha
            if (this.hasCaptcha) {
                objt['g-recaptcha-response'] = this.recaptchaResponse;
            }

            if (this.typeMessage === "sendSuggestionMessage") {
                objt.only_owner = this.formData.only_owner;
            }
            let error = null;

            if (error = this.validade(objt)) {
                let mess = "";
                if (error == "g-recaptcha-response") {
                    mess = this.text('Recaptcha inválida');
                } else {
                    mess = this.text('Todos os campos são obrigatórios');
                }
                this.messages.error(mess);
                return;
            }

            await api.POST(url, objt).then(res => res.json()).then(data => {
                this.messages.success(this.text('Dados enviados com sucesso'));
            });
        },
        async verifyCaptcha(response) {
            this.recaptchaResponse = response;
        },
        expiredCaptcha() {
            this.hasCaptcha = true;
            this.recaptchaResponse = '';
        },
        validade(objt) {
            let result = null;
            let ignore = ["copy", "anonimous", "only_owner"];

            if(!this.hasCaptcha){
                ignore.push("g-recaptcha-response");
            }

            if(this.formData.anonimous){
                ignore.push("name");
                ignore.push("email");
                this.formData.name = "";
                this.formData.email = "";
            }

            Object.keys(objt).forEach(function (item) {
                if (!objt[item] && !ignore.includes(item)) {
                    result = item;
                    return;
                }
            });
            return result;
        },
        initFormData(type) {
            this.typeMessage = type;
            this.formData = {
                name: $MAPAS.complaintSuggestionConfig.senderName,
                email: $MAPAS.complaintSuggestionConfig.senderEmail,
                type: "",
                message: "",
                anonimous: false,
                copy: false,
            }
        }
    },
});
