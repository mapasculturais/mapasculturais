app.component('registration-form', {
    template: $TEMPLATES['registration-form'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-form')
        return { text, hasSlot }
    },



    mounted() {
        const registration = this.registration;
        const self = this;
        globalThis.addEventListener('afterFetch', ({detail}) => {
            if(registration.singleUrl == detail.url) {
                self.category = Vue.readonly(self.registration.category);
                self.proponentType = Vue.readonly(self.registration.proponentType);
                self.range = Vue.readonly(self.registration.range);
            }
        })
    },

    data() {
        
        const category = Vue.readonly(this.registration.category);
        const proponentType = Vue.readonly(this.registration.proponentType);
        const range = Vue.readonly(this.registration.range);

        const hasCategory = this.registration.opportunity.registrationCategories?.length > 0;
        const hasProponentType = this.registration.opportunity.registrationProponentTypes?.length > 0;
        const hasRange = this.registration.opportunity.registrationRanges?.length > 0;

        return {
            category,
            proponentType,
            range,
            hasCategory,
            hasProponentType,
            hasRange,
        }
    },

    computed: {
       
    },
    
    
    methods: {
        isValid() {
            let valid = true;

            if(this.registration.opportunity.registrationCategories?.length > 0 && !this.category) {
                valid = false;
            }

            if(this.registration.opportunity.registrationProponentTypes?.length > 0 && !this.proponentType) {
                valid = false;
            }

            if(this.registration.opportunity.registrationRanges?.length > 0 && !this.range) {
                valid = false;
            }

            return valid;
        }
    },
});
