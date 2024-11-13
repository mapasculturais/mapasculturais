app.component('opportunity-evaluation-committee', {
    template: $TEMPLATES['opportunity-evaluation-committee'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        group: {
            type: String,
            default: 'group-admin'
        },
        showDisabled: {
            type: Boolean,
            default: false,
        },
        excludeFields: Array
    },

    computed: {
        query() {
            let query = {
                '@select': 'id,name,files.avatar,user',
                '@order': 'id ASC',
                '@limit': '25',
                '@page': '1',
                'type': 'EQ(1)',
            };

            if (this.reviewersId.length > 0) {
                const ids = this.reviewersId.join(',');
                query['id'] = `!IN(${ids})`;
            } else {
                delete query['id'];
            }

            return query;
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
        let ranges = this.entity.opportunity.registrationRanges?.map((range) => range.label);

        return {
            agentData: null,
            showReviewers: false,
            infosReviewers: {},
            queryString: 'id,name,files.avatar,user',
            selectCategories: [],
            registrationCategories: [
                'sem avaliações',
                ... (this.entity.opportunity.registrationCategories ?? [])
            ],
            registrationRanges: [
                ... (ranges ?? [])
            ],
            registrationProponentTypes: [
                ... (this.entity.opportunity.registrationProponentTypes ?? [])
            ],
            sendTimeOut: null,
            fetchConfigs: {},
            reviewersId: [],
            fetchFields: this.entity.fetchFields
        }
    },
    
    methods: {   
        showSummary(summary) {
            return summary ? Object.values(summary).some(value => value > 0) : false;
        },

        hasEvaluationConfiguration(agentId) {
            const propertiesToCheck = [
                'fetchCategories',
                'fetchProponentTypes',
                'fetchRanges',
                'fetch',
                'fetchSelectionFields'
            ];
        
            for (const property of propertiesToCheck) {
                if (this.entity[property] && this.entity[property][agentId] && Object.keys(this.entity[property][agentId]).length > 0) {
                    return true;
                }
            }
           
            for(let item in this.fetchFields[this.group]) {
                if(this.fetchFields[this.group][item].length > 0) {
                    return true;
                }
            }

            if(this.entity.valuersPerRegistration[this.group]) {
                return true;
            }
        
            return false;
        },
        
        selectAgent(agent) {
            const api = new API();
            let url = Utils.createUrl('evaluationMethodConfiguration', 'createAgentRelation', {id: this.entity.id});
            this.agentData = {
                group: this.group,
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
                this.infosReviewers = data.filter(reviewer => reviewer.group === this.group).map(reviewer => ({
                    ...reviewer,
                    isContentVisible: false,
                }));

                const pendingReviews = this.infosReviewers.filter((reviewer) => reviewer.status === -5);
                pendingReviews.sort((a, b) => {
                    if (a.agent.name < b.agent.name) {
                        return -1;
                    } else if (a.agent.name > b.agent.name) {
                        return 1;
                    } else {
                        return 0;
                    }
                });

                const acceptedReviews = this.infosReviewers.filter((reviewer) => reviewer.status !== -5);
                acceptedReviews.sort((a, b) => {
                    if (a.agent.name < b.agent.name) {
                        return -1;
                    } else if (a.agent.name > b.agent.name) {
                        return 1;
                    } else {
                        return 0;
                    }
                });

                this.infosReviewers = [...pendingReviews, ...acceptedReviews];

                this.reviewersId = this.infosReviewers.map((reviewer) => reviewer.agent.id);

                this.infosReviewers = this.infosReviewers.filter (reviewer => {
                    if (this.showDisabled) {
                        return reviewer.status === 8;
                    } else {
                        return reviewer.status !== 8;
                    }
                })
                this.showReviewers = Object.keys(this.infosReviewers).length > 0;
                this.ReviewerSelect = false;
                this.entity.agentRelations = data;
                this.loadFetchs();
            });
        },

        delReviewer(infoReviewer) {
            const agentId = infoReviewer.agent.id;
            const userId = infoReviewer.agentUserId;

            let userGroups = this.entity.agentRelations.filter(relation => relation.agentUserId === userId);
            
            const api = new API();
            let url = Utils.createUrl('evaluationMethodConfiguration', 'removeAgentRelation', {id: this.entity.id});
            this.agentData = {
                group: this.group,
                agentId: agentId,
            }; 

            api.POST(url, this.agentData).then(res => res.json()).then(data => {
                if (userGroups.length <= 1) {
                    this.delReviewerData(userId);
                }
                this.loadReviewers();
                this.entity.save();
            });
        },

        delReviewerData(userId) {
            const properties = [
                'fetch',
                'fetchSelectionFields',
                'fetchRanges',
                'fetchProponentTypes',
                'fetchCategories'
            ];

            properties.forEach(property => {
                if (this.entity[property]) {
                    if (this.entity[property][userId]) {
                        delete this.entity[property][userId];
                    }
                }
            });
        },

        disableOrEnableReviewer(infoReviewer) {
            let enableOrDisabled = infoReviewer.status === 8 ? 'enabled' : 'disabled';
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

        sendDefinition(field, userId, event = null, type) {
            const api = new API();
            let url = Utils.createUrl('evaluationMethodConfiguration', 'single', {id: this.entity.id});
            
            const fetchFieldMap = {
                fetch: 'fetch',
                categories: 'fetchCategories',
                ranges: 'fetchRanges',
                proponentTypes: 'fetchProponentTypes'
            };
              
            let fetchField = fetchFieldMap[type];

            if (event && event === 'sem avaliações' && this.entity[fetchField][userId].length > 1) {
                this.entity[fetchField][userId] = this.entity[fetchField][userId].filter((item) => item === 'sem avaliações');
            } else if (event && event !== 'sem avaliações' && this.entity[fetchField][userId].includes('sem avaliações')) {
                this.entity[fetchField][userId] = this.entity[fetchField][userId].filter((item) => item !== 'sem avaliações');
            }
    
            let args = {
                [fetchField]: this.entity[fetchField]
            };
    
            api.POST(url, args).then(res => res.json()).then(data => {
                const successMessages = {
                    addDistribution: 'addDistribution',
                    addCategory: 'addCategory',
                    addRange: 'addRange',
                    addProponentType: 'addProponentType',
                    removeCategory: 'removeCategory',
                    removeRange: 'removeRange',
                    removeProponentType: 'removeProponentType'
                };
        
                if (successMessages[field]) {
                    this.messages.success(this.text(successMessages[field]));
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

                    if(!this.entity.fetchRanges) {
                        this.entity.fetchRanges = {};
                        this.entity.fetchRanges[info.agentUserId] = [];
                    }

                    if(this.entity.fetchRanges && !this.entity.fetchRanges[info.agentUserId]) {
                        this.entity.fetchRanges[info.agentUserId] = [];
                    }

                    if(!this.entity.fetchProponentTypes) {
                        this.entity.fetchProponentTypes = {};
                        this.entity.fetchProponentTypes[info.agentUserId] = [];
                    }

                    if(this.entity.fetchProponentTypes && !this.entity.fetchProponentTypes[info.agentUserId]) {
                        this.entity.fetchProponentTypes[info.agentUserId] = [];
                    }

                    info.default = (this.entity.fetch[info.agentUserId] || this.entity.fetchCategories[info.agentUserId].length > 0 || this.entity.fetchRanges[info.agentUserId].length > 0 || this.entity.fetchProponentTypes[info.agentUserId].length > 0) ? false : true;

                });
            }
        },

        toggleContent(reviewerId) {
            const reviewer = this.infosReviewers.find(r => r.id === reviewerId);
            if (reviewer) {
                reviewer.isContentVisible = !reviewer.isContentVisible;
            }
        },

        expandAllToggles() {
            const allExpanded = this.infosReviewers.every(reviewer => reviewer.isContentVisible);

            this.infosReviewers.forEach(reviewer => {
                reviewer.isContentVisible = !allExpanded;
            });
        },
    },
});
