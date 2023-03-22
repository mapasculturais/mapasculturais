app.component('panel--registration-tabs', {
    template: $TEMPLATES['panel--registration-tabs'],

    mounted() {
        if (this.totalDrafts > 0) {
            this.showAlert = true;
        }
    },

    data() {
        let query = {
            '@permissions': '@control',
            'status': 'GT(0)',
            '@order': 'createTimestamp DESC',
        };

        if ($MAPAS.currentUserRoles.includes('admin')) {
            query['@permissions'] = 'view';
            query['user'] = 'EQ(@me)';
        }

        return {
            query,
            totalDrafts: $MAPAS.config.panelRegistrationTabs.totalDrafts,
            showAlert: false,
        }
    },
    
    methods: {
        changed(event) {
            console.log(event.tab);
            switch (event.tab.slug) {
                case 'sent':
                    this.query['status'] = 'GT(0)';
                    break;
                case 'notSent':
                    this.query['status'] = 'EQ(0)';
                    break;
            }
        },

        consoleLog(text){
            console.log(text)
        },

        closeAlert() {
            this.showAlert = false;
        },
    },
});
