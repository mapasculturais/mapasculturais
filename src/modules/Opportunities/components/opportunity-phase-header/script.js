app.component('opportunity-phase-header', {
    template: $TEMPLATES['opportunity-phase-header'],

    props: {
        phase: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-phase-header')
        return { text, hasSlot }
    },

    computed: {
        dateFrom () {
            const date = this.phase.registrationFrom || this.phase.evaluationFrom;
            return date?.date('2-digit year');
        },
        dateTo () {
            const date = this.phase.registrationTo || this.phase.evaluationTo;
            if (date && ((this.phase.isContinuousFlow || this.phase.opportunity?.isContinuousFlow) && (!this.phase.hasEndDate || !this.phase.opportunity?.hasEndDate))) {
                return false;
            }
            return date?.date('2-digit year');
        },
        publishDate () {
            const date = this.phase.publishTimestamp;
            return date?.date('2-digit year');
        },

        titleColClass() {
            let sum = 12;
            sum -= this.dateFrom ? 2 : 0;
            sum -= this.dateTo ? 2 : 0;
            sum -= this.publishDate ? 2 : 0;
            
            return `col-${sum}`;
        },

        title () {
            if(this.phase.isFirstPhase) {
                return this.text('periodo de inscricao');
            } else {
                const index = $MAPAS.opportunityPhases.findIndex(item => item.__objectType == this.phase.__objectType && item.id == this.phase.id) + 1;
                
                if (index) { 
                    return `${index}. ${this.phase.name}`;
                } else {
                    return this.phase.name;
                }
            }
        }
    }
});
