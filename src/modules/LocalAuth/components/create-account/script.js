app.component('create-account', {
    template: $TEMPLATES['create-account'],

    components: {
        VueRecaptcha
    },

    setup() {
        const text = Utils.getTexts('create-account')
        return { text }
    },

    data() {
        const globalState = useGlobalState();
        const terms = $MAPAS.config.LGPD;
        const termsQtd = Object.entries(terms).length;

        return {
            actualStep: globalState['stepper'] ?? 0,
            totalSteps: termsQtd + 2,
            terms,
            passwordRules: {},
            strongness: 0,
            strongnessClass: 'fraco',
            slugs: [],
            email: '',
            cpf: '',
            password: '',
            confirmPassword: '',
            agent: null,
            recaptchaResponse: '',
            created: false,
            emailSent: false,
        }
    },

    props: {
        config: {
            type: String,
            required: true
        }
    },

    mounted() {
        let api = new API();
        api.GET($MAPAS.baseURL + "auth/passwordvalidationinfos").then(async response => response.json().then(validations => {
            this.passwordRules = validations.passwordRules;
        }));
    },

    destroyed() {
        window.removeEventListener('scroll');
    },

    computed: {
        arraySteps() {
            let steps = Object.entries(this.terms).length + 2;
            let totalSteps = [];
            for (let i = 0; i < steps; i++) {
                totalSteps.push(i);
            }
            return totalSteps;
        },

        step() {
            return this.actualStep;
        },

        configs() {
            return JSON.parse(this.config);
        },

        passwordStrongness() {
            if (this.password) {
                let passwordMustHaveCapitalLetters = /[A-Z]/;
                let passwordMustHaveLowercaseLetters = /[a-z]/;
                let passwordMustHaveSpecialCharacters = /[$@$!%*#?&\.\,\:<>+\_\-\"\'()]/;
                let passwordMustHaveNumbers = /[0-9]/;
                let minimumPasswordLength = 8;
                let pwd = this.password;
                let rules = [];

                if (this.passwordRules.passwordMustHaveCapitalLetters) {
                    rules.push(passwordMustHaveCapitalLetters);
                }

                if (this.passwordRules.passwordMustHaveLowercaseLetters) {
                    rules.push(passwordMustHaveLowercaseLetters);
                }

                if (this.passwordRules.passwordMustHaveSpecialCharacters) {
                    rules.push(passwordMustHaveSpecialCharacters);
                }

                if (this.passwordRules.passwordMustHaveNumbers) {
                    rules.push(passwordMustHaveNumbers);
                }

                if (this.passwordRules.minimumPasswordLength) {
                    minimumPasswordLength = this.passwordRules.minimumPasswordLength
                }

                let rulesLength = rules.length;
                let prog = rules.reduce(function (accumulator, test) {
                    return accumulator + (test.test(pwd) ? 1 : 0);
                }, 0);

                let percentToAdd = 100 / (rulesLength + 1);
                let currentPercent = prog * 100 / (rulesLength + 1);

                if (pwd.length > minimumPasswordLength - 1) {
                    currentPercent = currentPercent + percentToAdd;
                }

                let strongness = currentPercent.toFixed(0)
                if (strongness >= 0 && strongness <= 40) {
                    this.strongnessClass = 'fraco';
                }
                if (strongness >= 40 && strongness <= 90) {
                    this.strongnessClass = 'medio';
                }
                if (strongness >= 90 && strongness <= 100) {
                    this.strongnessClass = 'forte';
                }

                return currentPercent.toFixed(0);
            } else {
                return 0;
            }

        }
    },

    methods: {
        startAgent() {
            this.agent = Vue.ref(new Entity('agent'));
            this.agent.type = 1;
            this.agent.terms = { area: [] }
        },

        async nextStep() {
            this.goToStep(this.actualStep + 1);
        },

        previousStep() {
            this.goToStep(this.actualStep - 1);
        },

        async goToStep(step) {
            const globalState = useGlobalState();

            if (this.actualStep == 0) {
                if (await this.validateFields()) {
                    this.actualStep = step;
                    if (step == this.totalSteps - 1) {
                        this.startAgent();
                    }
                }
            } else {
                if (step == this.totalSteps - 1) {
                    this.startAgent();
                }
                this.actualStep = step;
            }

            if (this.actualStep >= this.totalSteps) {
                this.actualStep = this.totalSteps;
            } else if (this.actualStep <= 0) {
                this.actualStep = 0;
            }

            globalState['stepper'] = this.actualStep;
            window.scrollTo(0, 0);
        },

        /* Terms */
        acceptTerm(slug) {
            this.slugs.push(slug);
        },

        /* Do register */
        async register() {
            let api = new API();

            if (this.validateAgent()) {
                let dataPost = {
                    'name': this.agent.name,
                    'email': this.email,
                    'cpf': this.cpf,
                    'password': this.password,
                    'confirm_password': this.confirmPassword,
                    'slugs': this.slugs,
                    'g-recaptcha-response': this.recaptchaResponse,
                    'agentData': {
                        'name': this.agent.name,
                        'terms:area': this.agent.terms.area,
                        'shortDescription': this.agent.shortDescription,
                    },
                }

                await api.POST($MAPAS.baseURL + "autenticacao/register", dataPost).then(response => response.json().then(dataReturn => {
                    if (dataReturn.error) {
                        this.throwErrors(dataReturn.data);
                    } else {
                        if (dataReturn.redirectTo) {
                            window.location = dataReturn.redirectTo;
                        }
                        this.created = true;
                        if (dataReturn.emailSent) {
                            this.emailSent = true;
                        }
                        window.scrollTo(0, 0);
                    }
                }));
            }
        },

        /* Cancel register */
        cancel() {
            this.strongnessClass = 'fraco';
            this.email = '';
            this.cpf = '';
            this.password = '';
            this.confirmPassword = '';
            this.agent = null;
            this.slugs = [];
            this.goToStep(0);
        },

        /* Validações */

        async verifyCaptcha(response) {
            this.recaptchaResponse = response;
        },

        expiredCaptcha() {
            this.recaptchaResponse = '';
        },

        async validateFields() {
            let api = new API();
            let success = true;
            let data = {
                'cpf': this.cpf,
                'email': this.email,
                'password': this.password,
                'confirm_password': this.confirmPassword,
                'g-recaptcha-response': this.recaptchaResponse,
            }
            await api.POST($MAPAS.baseURL + "autenticacao/validate", data).then(response => response.json().then(dataReturn => {
                if (dataReturn.error) {
                    this.throwErrors(dataReturn.data);
                    success = false;
                } else {
                    this.recaptchaResponse = '';
                }
            }));

            return success;
        },

        throwErrors(errors) {
            const messages = useMessages();

            if (this.recaptchaResponse !== '') {
                grecaptcha.reset();
                this.expiredCaptcha();
            }

            for (let key in errors) {
                if (errors[key] instanceof Array) {
                    for (let val of errors[key]) {
                        messages.error(val);
                    }
                }
                if (!(errors[key] instanceof Array)) {
                    for (let _key in errors[key]) {
                        if (errors[key][_key] instanceof Array) {
                            for (let _val of errors[key][_key]) {
                                messages.error(_val);
                            }
                        } else {
                            messages.error(errors[key][_key]);
                        }
                    }
                }
            }
        },

        validateAgent() {
            let errors = {
                'agent': [],
            };
            if (!this.agent.name) {
                errors.agent.push(__('Nome obrigatório', 'create-account'));
            }
            if (!this.agent.shortDescription) {
                errors.agent.push(__('Descrição obrigatória', 'create-account'));
            }
            if (this.agent.terms.area.length == 0) {
                errors.agent.push(__('Área de atuação obrigatória', 'create-account'));
            }

            // Validação de campos obrigatórios das taxonomias
            Object.keys($TAXONOMIES).forEach(taxonomy => {
                const t = $TAXONOMIES[taxonomy];
                if (t.required  && t.entities.includes('MapasCulturais\\Entities\\Agent')) {
                    if(this.agent.terms[taxonomy].length == 0) {
                        errors.agent.push(`${t.description} ${__('required', 'create-account')}`);
                    }
                }
            });

            if (errors.agent.length > 0) {
                this.throwErrors(errors);
                return false;
            }
            return true;
        },

        togglePassword(id, event) {
            if (document.getElementById(id).type == 'password') {
                event.target.style.background = "url('https://api.iconify.design/carbon/view-off-filled.svg') no-repeat center center / 22.5px"
                document.getElementById(id).type = 'text';
            } else {
                event.target.style.background = "url('https://api.iconify.design/carbon/view-filled.svg') no-repeat center center / 22.5px"
                document.getElementById(id).type = 'password';
            }
        },
    },
});
