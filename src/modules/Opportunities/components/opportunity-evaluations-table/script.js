app.component('opportunity-evaluations-table' , {
    template: $TEMPLATES['opportunity-evaluations-table'],
    props: {
        phase: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        user: {
            type: [String, Number],
            required: true
        }
    },
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-evaluations-table');
        return { text }
    },
    data () {
      return {
      }
    },
    methods: {
        canSee(action){
            if(this.phase.opportunity.currentUserPermissions[action]){
                return true;
            }
            return false
        },
        isFuture() {
            return this.phase.evaluationFrom?.isFuture();
        },

        isHappening() {
            return this.phase.evaluationFrom?.isPast() && this.phase.evaluationTo?.isFuture();
        },

        isPast() {
            return this.phase.evaluationTo?.isPast();
        }
    }
});