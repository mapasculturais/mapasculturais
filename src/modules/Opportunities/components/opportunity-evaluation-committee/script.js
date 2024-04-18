app.component('opportunity-evaluation-committee', {
    template: $TEMPLATES['opportunity-evaluation-committee'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    setup() {
        const text = Utils.getTexts('opportunity-evaluations-list');
        return { text }
    },

    computed: {
        query() {
            return {
                '@opportunity': this.entity.id,
                '@limit': 50,
                '@page': 1
            };
        },
        select() {
            return "id,owner,agent,agentUserId";
        },
    },

    mounted() {
        this.loadReviewers();
    },

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('opportunity-evaluation-committee')
        return { text, messages }
    },

    data() {
        return {
            agentData: null,
            showReviewers: false,
            infosReviewers: {},
            queryString: 'id,name,files.avatar,user',
            selectCategories: [],
            registrationCategories: [
                'sem avaliações',
                ... (this.entity.opportunity.registrationCategories ?? [])
            ]
        }
    },
    
    methods: {   
        selectAgent(agent) {
            const api = new API();
            let url = Utils.createUrl('evaluationMethodConfiguration', 'createAgentRelation', {id: this.entity.id});
            this.agentData = {
                group: 'group-admin',
                agentId: agent._id,
                has_control: true
            }; 

            api.POST(url, this.agentData).then(res => res.json()).then(data => {
                this.loadReviewers();
                this.loadFetchs();
            });
        },
        
        loadReviewers() {
            let args = {
                '@opportunity': this.entity.opportunity.id,
                '@limit': 50,
                '@page': 1,
            };

            const api = new API('opportunity');
            let url = api.createApiUrl('evaluationCommittee', args);
            
            api.GET(url).then(res => res.json()).then(data => {
                this.infosReviewers = data;
                this.showReviewers = !!this.infosReviewers;
                this.ReviewerSelect = false;
                this.loadFetchs();
            });
        },

        delReviewer(agent) {
            const api = new API();
            let url = Utils.createUrl('evaluationMethodConfiguration', 'removeAgentRelation', {id: this.entity.id});
            this.agentData = {
                group: 'group-admin',
                agentId: agent.id,
            }; 

            api.POST(url, this.agentData).then(res => res.json()).then(data => {
                this.loadReviewers();
            });
        },

        disableOrEnableReviewer(infoReviewer) {
            let enableOrDisabled = infoReviewer.status === 8 ? 'enabled' : 'disabled';;
            const api = new API();
            let url;
            let relationData = {
                relationId: infoReviewer.id,
            };

            if(enableOrDisabled === 'disabled') {
                url = Utils.createUrl('evaluationMethodConfiguration', 'disableValuer', {id: this.entity.id});
            } else {
                url = Utils.createUrl('evaluationMethodConfiguration', 'enableValuer', {id: this.entity.id});
            }

            api.POST(url, relationData).then(res => res.json()).then(data => {
                this.loadReviewers();
            });
        },

        reopenEvaluations(user) {
            const api = new API();
            let url = Utils.createUrl('opportunity', 'reopenEvaluations');
            let data = {
                uid: user,
                opportunityId: this.entity.opportunity.id
            };

            api.POST(url, data).then(res => res.json()).then(data => {
                this.messages.success(this.text('reopenEvaluationsSuccess'));
                this.loadReviewers();
            });
        },

        buttonText(status) {
            return status === 8 ? this.text('enable') : this.text('disable');
        },

        sendDefinition(field, userId, event = null) {
            const api = new API();
            let url = Utils.createUrl('evaluationMethodConfiguration', 'single', {id: this.entity.id});
            

            if (event && event == 'sem avaliações' && this.entity.fetchCategories[userId].length > 1) {
                this.entity.fetchCategories[userId] = this.entity.fetchCategories[userId].filter((category) => category == 'sem avaliações');
            } else if (event && event != 'sem avaliações' && this.entity.fetchCategories[userId].includes('sem avaliações')) {
                this.entity.fetchCategories[userId] = this.entity.fetchCategories[userId].filter((category) => category != 'sem avaliações');
            }

            let testData = {
                fetch: this.entity.fetch,
                fetchCategories: this.entity.fetchCategories
            };
            
            api.POST(url, testData).then(res => res.json()).then(data => {
                switch (field) {
                    case 'addDistribution':
                        this.messages.success(this.text('addDistribution'));
                        break;
                    case 'addCategory':
                        this.messages.success(this.text('addCategory'));
                        break;
                    case 'removeCategory':
                        this.messages.success(this.text('removeCategory'));
                        break;
                }
                this.loadReviewers();
            });
        },

        loadFetchs() {
            if(this.infosReviewers) {
                this.infosReviewers.forEach(info => {
                    if(!this.entity.fetch) {
                        this.entity.fetch = {};
                        this.entity.fetch[info.agentUserId] = ""
                    }

                    if(this.entity.fetch && !this.entity.fetch[info.agentUserId]) {
                        this.entity.fetch[info.agentUserId] = "";
                    }
    
                    if(!this.entity.fetchCategories) {
                        this.entity.fetchCategories = {};
                        this.entity.fetchCategories[info.agentUserId] = [];
                    }
    
                    if(this.entity.fetchCategories && !this.entity.fetchCategories[info.agentUserId]) {
                        this.entity.fetchCategories[info.agentUserId] = [];
                    }

                    info.default = (this.entity.fetch[info.agentUserId] || this.entity.fetchCategories[info.agentUserId].length > 0) ? false : true;

                });
            }
        },
    },
});
