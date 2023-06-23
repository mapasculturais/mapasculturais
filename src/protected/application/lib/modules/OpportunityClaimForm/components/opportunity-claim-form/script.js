app.component('opportunity-claim-form', {
    template: $TEMPLATES['opportunity-claim-form'],
    setup() { 
        const text = Utils.getTexts('opportunity-claim-form')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },

    },

    data() {
        return {
            claim: {
                registration_id:$MAPAS.config.opportunityClaimForm.registrationId
            },
        }
    },
    methods: {
        isActive(){
            if(this.entity.opportunity.status > 0 && this.entity.opportunity.publishedRegistrations && this.entity.opportunity.claimDisabled === "0"){
                return true;
            }
            return false;
        },
        sendClain(){
            let api = new API();
            let url = Utils.createUrl('opportunity', 'sendOpportunityClaimMessage');

            api.POST(url, this.claim).then(res => res.json()).then(data => {
                messages.success(this.text('Solicitação de recurso enviada'));
            });
          
        }
    },

    computed: {

        modalTitle() {
            return 'Solicitar Recurso';

        },
    },
});

