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

        if (Object.values($MAPAS.currentUserRoles).includes('admin')) {
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
            switch (event.tab.slug) {
                case 'sent':                    
                    if (Object.values($MAPAS.currentUserRoles).includes('admin')) {
                        this.query['@permissions'] = 'view';
                    }else{
                        this.query['@permissions'] = '@control';
                    }
                    this.query['status'] = 'GT(0)';
                    break;
                case 'notSent':
                    this.query['status'] = 'EQ(0)';
                    this.query['@permissions'] = 'view';
                    break;
            }
        },
    },
});
