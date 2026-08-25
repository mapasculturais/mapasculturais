app.component('password-strongness', {
    template: $TEMPLATES['password-strongness'],

    components: {
        VueRecaptcha
    },

    props: {
        password: {
            type: String,
            required: true
        }
    },

    setup() {
        const text = Utils.getTexts('password-strongness')
        return { text }
    },

    mounted() {
        let api = new API();
        api.GET($MAPAS.baseURL + "auth/passwordvalidationinfos").then(async response => response.json().then(validations => {
            
            this.passwordRules = validations.passwordRules;

            this.passwordRules.minimumPasswordLength = this.passwordRules.minimumPasswordLength || 8;
        }));
    },

    data() {
        const passwordRules = {};

        const passwordMustHaveCapitalLetters = /[A-Z]/;
        const passwordMustHaveLowercaseLetters = /[a-z]/;
        const passwordMustHaveSpecialCharacters = /[$@$!%*#?&\.\,\:<>+\_\-\"\'()]/;
        const passwordMustHaveNumbers = /[0-9]/;

        return {
            passwordRules,
            passwordMustHaveCapitalLetters,
            passwordMustHaveLowercaseLetters,
            passwordMustHaveSpecialCharacters,
            passwordMustHaveNumbers
        }
    },

    methods: {
        getErrors() {
            const errors = [];

            if (this.password.length < this.passwordRules.minimumPasswordLength) {
                errors.push(this.text('{num} caracteres').replace('{num}', this.passwordRules.minimumPasswordLength));
            }

            if (this.passwordRules.passwordMustHaveCapitalLetters && !this.passwordMustHaveCapitalLetters.test(this.password)) {
                errors.push(this.text('pelo menos uma letra maiúscula'));
            }

            if (this.passwordRules.passwordMustHaveLowercaseLetters && !this.passwordMustHaveLowercaseLetters.test(this.password)) {
                errors.push(this.text('pelo menos uma letra minúscula'));
            }

            if (this.passwordRules.passwordMustHaveSpecialCharacters && !this.passwordMustHaveSpecialCharacters.test(this.password)) {
                errors.push(this.text('um caracter especial'));
            }

            if (this.passwordRules.passwordMustHaveNumbers && !this.passwordMustHaveNumbers.test(this.password)) {
                errors.push(this.text('um número'));
            }

            return errors.join(', ');
        },

        rules() {
            const rules = [];
            
            if (this.passwordRules.passwordMustHaveCapitalLetters) {
                rules.push(this.passwordMustHaveCapitalLetters);
            }

            if (this.passwordRules.passwordMustHaveLowercaseLetters) {
                rules.push(this.passwordMustHaveLowercaseLetters);
            }

            if (this.passwordRules.passwordMustHaveSpecialCharacters) {
                rules.push(this.passwordMustHaveSpecialCharacters);
            }

            if (this.passwordRules.passwordMustHaveNumbers) {
                rules.push(this.passwordMustHaveNumbers);
            }

            return rules;
        },
        strongness() {
            if (this.password) {
                const minimumPasswordLength = this.passwordRules.minimumPasswordLength;
                const rules = this.rules();
                
                const rulesLength = rules.length;
                const percentToAdd = 100 / (rulesLength + 1);
                const pass = this.password;

                let prog = 0;

                for(let rule of rules) {
                    if(rule.test(this.password)) {
                        prog++;
                    }
                }

                let currentPercent = prog * 100 / (rulesLength + 1);

                if (this.password.length > minimumPasswordLength - 1) {
                    currentPercent = currentPercent + percentToAdd;
                }

                return currentPercent.toFixed(0)
            } else {
                return 0;
            }

        },

        strongnessClass() {
            if (this.strongness <= 40) {
                return 'fraco';
            } else if (this.strongness <= 85) {
                return 'medio';
            } else {
                return 'forte';
            }
        }
    },
});
