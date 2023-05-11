app.component('complaint-suggestion', {
    template: $TEMPLATES['complaint-suggestion'],
    components: {
        VueRecaptcha
    },
    setup() {
        const text = Utils.getTexts('complaint-suggestion')
        return { text }
    },
    props: {
        entity: {
            type: Entity,
            required: true,
        }
    },

    data() {
        let isAuth = $MAPAS.complaintSuggestionConfig.isAuth;
        let typeMessage = "";
        let sitekey = $MAPAS.complaintSuggestionConfig.recaptcha.sitekey;
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

        return { definitions, options, typeMessage, sitekey, recaptchaResponse, formData, isAuth }
    },

    methods: {
        async send() {

            const api = new API(this.entity.__objectType);
            let url = api.createUrl(this.typeMessage);

            let objt = this.formData;
            objt.entityId = this.entity.id;
            objt['g-recaptcha-response'] = this.recaptchaResponse;

            if (this.typeMessage === "sendSuggestionMessage") {
                objt.only_owner = this.formData.only_owner;
            }
            let error = null;

            if (error = this.validade(objt)) {
                let mess = "";
                if (error == "g-recaptcha-response") {
                    mess = this.text('Recaptcha inválida');
                } else {
                    mess = this.text('Todos os campos são obrigatorio');
                }
                messages.error(mess);
                return;
            }

            await api.POST(url, objt).then(res => res.json()).then(data => {
                messages.success(this.text('Dados enviados com suscesso'));
            });
        },
        async verifyCaptcha(response) {
            this.recaptchaResponse = response;
        },
        expiredCaptcha() {
            this.recaptchaResponse = '';
        },
        validade(objt) {
            let result = null;
            let ignore = ["copy", "anonimous", "only_owner"];
            Object.keys(objt).forEach(function (item) {
                if (!objt[item] && !ignore.includes(item)) {
                    result = item;
                    return;
                }
            });
            return result;
        },
        initFormData() {
            this.formData = {
                name: "",
                email: "",
                type: "",
                message: "",
                anonimous: false,
                copy: false,
            }
        }
    },
});
