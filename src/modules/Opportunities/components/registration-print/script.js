/**
 * Vue Lifecycle
 * 1. setup
 * 2. beforeCreate
 * 3. created
 * 4. beforeMount
 * 5. mounted
 * 
 * // sempre que há modificação nos dados
 *  - beforeUpdate
 *  - updated
 * 
 * 6. beforeUnmount
 * 7. unmounted                  
 */

app.component('registration-print', {
    template: $TEMPLATES['registration-print'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined', 'agentChanged'],

    setup() {
        const text = Utils.getTexts('registration-print');
        const global = useGlobalState();
        return { text, global };
    },

    props: {
        registration: {
            type: Entity,
            required: true,
        },
    },
    
    data() {
        return {
            loading: false,
            changingAgent: false,
            selectedAgent: null,
            errorMessage: null,
        }
    },

    computed: {
        isSaasSuperAdmin() {
            return !!this.global?.auth?.is?.('saasSuperAdmin');
        },
    },
    
    methods: {
        print() {
            const self = this;
            self.loading = true;
            var iframe = this.$refs.printIframe;

            iframe.addEventListener("load", function(e) {      
                setTimeout(() => {
                    self.loading = false;
                }, 1000);
            });

            iframe.src = Utils.createUrl('registration', 'registrationPrint', [this.registration.id]);
        },
        
        exportPDF() {
            const url = Utils.createUrl('registration', 'exportPDF', [this.registration.id]);
            window.open(url, '_blank');
        },
        
        downloadZip() {
            const url = Utils.createUrl('registration', 'createZipFiles', [this.registration.id]);
            window.open(url, '_blank');
        },

        openChangeAgentModal(modal) {
            this.selectedAgent = null;
            this.errorMessage = null;
            modal.open();
        },

        selectAgent(agent) {
            this.selectedAgent = agent;
            this.errorMessage = null;
        },

        async confirmChangeAgent(modal) {
            if (!this.selectedAgent?.id) {
                this.errorMessage = this.text('selecioneAgente');
                return;
            }

            this.changingAgent = true;
            this.errorMessage = null;

            try {
                const data = await this.registration.invoke('changeLinkedAgent', {
                    ownerId: this.selectedAgent.id,
                });

                this.$emit('agentChanged', this.registration);
                modal.close();
                // Recarrega para refletir avatar/nome, snapshot agentsData e sumir o aviso
                window.location.reload();
            } catch (error) {
                const message = error?.data || error?.message || this.text('erroTroca');
                this.errorMessage = typeof message === 'string' ? message : this.text('erroTroca');
            } finally {
                this.changingAgent = false;
            }
        },
    },
});
