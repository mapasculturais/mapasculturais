app.component('opportunity-claim-form', {
    template: $TEMPLATES['opportunity-claim-form'],
    setup() { 
        const text = Utils.getTexts('opportunity-claim-form')
        return { text }
    },

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },
    
    data() {
        return {
            claim: {
                message: ''
            },
        }
    },
    methods: {
        close(modal){
            this.claim.message = '';
            modal.close();
        },
        isActive(){
            const opportunity = this.registration.opportunity;
            if(this.registration.status > 0 && opportunity.publishedRegistrations && !opportunity.claimDisabled){
                return true;
            }
            return false;
        },
        async sendClain(modal){
            const messages = useMessages();
            let api = new API();
            let url = Utils.createUrl('opportunity', 'sendOpportunityClaimMessage', {registration_id: this.registration.id});

            await api.POST(url, this.claim).then(data => {
                messages.success(this.text('Solicitação de recurso enviada'));
                this.close(modal);
            });
        }
    },

    computed: {
        modalTitle() {
            return 'Solicitar Recurso';

        },
    },
});

