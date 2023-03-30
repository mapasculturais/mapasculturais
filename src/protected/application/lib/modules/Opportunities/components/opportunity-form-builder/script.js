app.component('opportunity-form-builder' , {
    template: $TEMPLATES['opportunity-form-builder'],
    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-form-builder');
        return { text }
    },
    data () {
      return {
          descriptionsOpportunity: null
      }
    },
    mounted () {
        this.descriptionsOpportunity = $DESCRIPTIONS.opportunity;
    },
    computed: {
        registrationFrom () {
            return this.entity.registrationFrom?.date('2-digit year');
        },
        registrationTo () {
            return this.entity.registrationTo?.date('2-digit year');
        },
        title () {
            if(this.entity.isFirstPhase) {
                return '1. Período de inscrição';
            } else {
                const index = $MAPAS.opportunityPhases.findIndex(item => item.__objectType == 'opportunity' && item.id == this.entity.id) + 1;
                
                if (index) { 
                    return `${index}. ${this.entity.name}`;
                } else {
                    return this.entity.name;
                }
            }
        }
    }
});