app.component('opportunity-form-builder' , {
    template: $TEMPLATES['opportunity-form-builder'],
    props: {
        entity: {
            type: Entity,
            required: true
        },
        isFirstPhase: {
            type: Boolean,
            required: true
        },
    },
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-form-builder');
        return { text }
    },
    mounted () {
        console.log(this.entity);
    },
    computed: {
        getDateRegistrationFrom () {
            return new McDate(this.entity.registrationFrom._date).date('2-digit year');
        },
        getDateRegistrationTo () {
            return new McDate(this.entity.registrationTo._date).date('2-digit year');
        },
        getTitleForm () {
            if(this.isFirstPhase) {
                return 'Período de inscrição';
            } else {
                return this.entity.name;
            }
        }
    }
});