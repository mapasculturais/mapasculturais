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
                'parent': 'NULL()'
            };

            if (this.reviewersId.length > 0) {
                const ids = this.reviewersId.join(',');
                query['id'] = `!IN(${ids})`;
            } else {
                delete query['id'];
            }

            return query;
        },

        allExpanded() {
            return this.infosReviewers.every(reviewer => reviewer.isContentVisible);
        },

        select() {
            return "id,owner,agent,agentUserId";
        },

        sortedReviewers () {
            return (this.infosReviewers ?? [])
                .slice()
                .sort((a, b) => {
                    const aName = a.agent?.name.toLocaleLowerCase() ?? '';
                    const bName = b.agent?.name.toLocaleLowerCase() ?? '';
                    return aName.localeCompare(bName);
                });
        },

        // Garante que os filtros globais da comissão sejam passados corretamente
        // mesmo após recarregar a página (F5)
        groupFiltersForComponent() {
            // Garante que fetchFields está inicializado
            if (!this.entity.fetchFields) {
                this.entity.fetchFields = {};
            }
            
            // Retorna os filtros globais do grupo atual
            return this.entity.fetchFields?.[this.group] || null;
        },

        commissionDistributionRule() {
            const src = this.entity.fetchFields?.[this.group] || {};
            const categories = Array.isArray(src.category) ? [...src.category] : [];
            const proponentTypes = Array.isArray(src.proponentType) ? [...src.proponentType] : [];
            const ranges = Array.isArray(src.range) ? [...src.range] : [];
            const distribution = typeof src.distribution == 'string' ? src.distribution : '';
            const sentTimestamp = (src.sentTimestamp && typeof src.sentTimestamp == 'object')
                ? { from: src.sentTimestamp.from || '', to: src.sentTimestamp.to || '' }
                : { from: '', to: '' };
            const fields = {};

            Object.entries(src).forEach(([key, value]) => {
                if (['category', 'proponentType', 'range', 'distribution', 'sentTimestamp'].includes(key)) return;
                if (Array.isArray(value) && value.length > 0) fields[key] = [...value];
            });

            return { categories, proponentTypes, ranges, distribution, sentTimestamp, fields };
        }
    },

    mounted() {
        // Garante que fetchFields está inicializado
        if (!this.entity.fetchFields) {
            this.entity.fetchFields = {};
        }
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
            infosReviewers: [],
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
            fetchFields: this.entity.fetchFields,
            showRegistrationListFlag: {},
            evaluatorDistributionRules: {},
            lastParentFilters: null
        }
    },
    
    methods: {   
        showSummary(summary) {
            return summary ? Object.values(summary).some(value => value > 0) : false;
        },

        showRegistrationList(infoReviewer) {
            return this.showRegistrationListFlag[infoReviewer.id] || !!infoReviewer.registrationListText;
        },

        changeShowRegistrationListFlag($event, infoReviewer) {
            if(!$event) {
                infoReviewer.registrationListText = '';
            }
        },

        hasEvaluationConfiguration(infoReviewer) {
            const summary = infoReviewer?.metadata?.summary || {};

            if(summary.pending || summary.started || summary.sent || summary.completed) {
                return true;
            }

            const agentId = infoReviewer.agentUserId;
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
        
        replaceReviewer(agent, relation) {
            const api = new API();
            let url = Utils.createUrl('evaluationMethodConfiguration', 'replaceValuer', {id: this.entity.id});
            
            let evaluatorData = {
                newValuerAgentId: agent.id,
                relation: relation.id,
            };

            api.POST(url, evaluatorData).then(res => res.json()).then(data => {
                this.messages.success(this.text('reviewerReplaced'));
                this.loadReviewers();
                this.loadFetchs();
                
            });
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
                '@order': 'name ASC',
                '@page': 1,
            };

            const api = new API('opportunity');
            let url = api.createApiUrl('evaluationCommittee', args);
            
            api.GET(url).then(res => res.json()).then(data => {
                const expandedIds = (this.infosReviewers || [])
                    .filter(reviewer => reviewer.isContentVisible)
                    .map(reviewer => reviewer.id);

                this.infosReviewers = data.filter(reviewer => reviewer.group === this.group).map(reviewer => {
                    if (!reviewer.metadata) {
                        reviewer.metadata = {};
                    }
                    
                    if (reviewer.metadata.registrationListExclusive == undefined || reviewer.metadata.registrationListExclusive == null) {
                        reviewer.metadata.registrationListExclusive = false;
                    } else {
                        reviewer.metadata.registrationListExclusive = Boolean(reviewer.metadata.registrationListExclusive);
                    }
                    
                    return {
                        ...reviewer,
                        isContentVisible: expandedIds.indexOf(reviewer.id) != -1,
                        registrationListText: this.formatRegistrationList(reviewer.metadata?.registrationList),
                    };
                });

                for(let infoReviewer of this.infosReviewers) {
                    this.showRegistrationListFlag[infoReviewer.id] = !!infoReviewer.registrationListText;
                }

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
                        this.entity[property][userId] = undefined;
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
            if(this.infosReviewers?.length > 0) {
                const keys = ['fetch', 'fetchCategories', 'fetchRanges', 'fetchProponentTypes', 'fetchSelectionFields'];
                
                this.infosReviewers.forEach(info => {
                    for (const key of keys) {
                        if (!this.entity[key]) {
                            this.entity[key] = {};
                        } else if (Array.isArray(this.entity[key])) {
                            if (this.entity[key].length === 0) {
                                this.entity[key] = {};
                            } else {
                                const entries = Object.entries(this.entity[key]).filter(([k, value]) => value != null);
                                this.entity[key] = Object.fromEntries(entries);
                            }
                        }

                        if (this.entity[key] && this.entity[key][info.agentUserId] === undefined) {
                            if (key === 'fetch') {
                                this.entity[key][info.agentUserId] = '';
                            } else if (key === 'fetchSelectionFields') {
                                this.entity[key][info.agentUserId] = {};
                            } else {
                                this.entity[key][info.agentUserId] = [];
                            }
                        }
                    }

                    this.evaluatorDistributionRules[info.agentUserId] = this.getEvaluatorDistributionRule(info.agentUserId);
                    info.default = (this.entity.fetch[info.agentUserId] || this.entity.fetchCategories[info.agentUserId].length > 0 || this.entity.fetchRanges[info.agentUserId].length > 0 || this.entity.fetchProponentTypes[info.agentUserId].length > 0) ? false : true;
                });
            }
        },

        getEvaluatorDistributionRule(agentUserId) {
            return {
                categories: Array.isArray(this.entity.fetchCategories?.[agentUserId]) ? [...this.entity.fetchCategories[agentUserId]] : [],
                proponentTypes: Array.isArray(this.entity.fetchProponentTypes?.[agentUserId]) ? [...this.entity.fetchProponentTypes[agentUserId]] : [],
                ranges: Array.isArray(this.entity.fetchRanges?.[agentUserId]) ? [...this.entity.fetchRanges[agentUserId]] : [],
                distribution: typeof this.entity.fetch?.[agentUserId] == 'string' ? this.entity.fetch[agentUserId] : '',
                sentTimestamp: { from: '', to: '' },
                fields: this.entity.fetchSelectionFields?.[agentUserId] && typeof this.entity.fetchSelectionFields[agentUserId] == 'object'
                    ? { ...this.entity.fetchSelectionFields[agentUserId] }
                    : {}
            };
        },

        async onEvaluatorDistributionRuleChange(rule, infoReviewer) {
            if (!rule || !infoReviewer) {
                return;
            }

            const relationId = infoReviewer.id;
            const agentUserId = infoReviewer.agentUserId;

            const categories = Array.isArray(rule.categories) && rule.categories.length > 0 ? rule.categories : null;
            const proponentTypes = Array.isArray(rule.proponentTypes) && rule.proponentTypes.length > 0 ? rule.proponentTypes : null;
            const ranges = Array.isArray(rule.ranges) && rule.ranges.length > 0 ? rule.ranges : null;
            const distribution = typeof rule.distribution == 'string' && rule.distribution.trim() ? rule.distribution.trim() : null;
            const selectionFields = rule.fields && typeof rule.fields == 'object' && Object.keys(rule.fields).length > 0 ? rule.fields : null;

            const url = Utils.createUrl('evaluationMethodConfiguration', 'setValuerFilters', { id: this.entity.id });
            const data = {
                relationId,
                categories,
                proponentTypes,
                ranges,
                distribution,
                selectionFields
            };

            try {
                const api = new API();
                await api.POST(url, data);
                this.syncEntityFromEvaluatorRule(agentUserId, rule);
                this.messages.success(this.text('modificações salvas'));
                this.loadReviewers();
            } catch (error) {
                console.error('Erro ao salvar filtros do avaliador:', error);
            }
        },

        syncEntityFromEvaluatorRule(agentUserId, rule) {
            if (!this.entity.fetchCategories) {
                this.entity.fetchCategories = {};
            }

            if (!this.entity.fetchProponentTypes) {
                this.entity.fetchProponentTypes = {};
            }

            if (!this.entity.fetchRanges) {
                this.entity.fetchRanges = {};
            }

            if (!this.entity.fetch) {
                this.entity.fetch = {};
            }

            if (!this.entity.fetchSelectionFields) {
                this.entity.fetchSelectionFields = {};
            }

            this.entity.fetchCategories[agentUserId] = Array.isArray(rule.categories) ? rule.categories : [];
            this.entity.fetchProponentTypes[agentUserId] = Array.isArray(rule.proponentTypes) ? rule.proponentTypes : [];
            this.entity.fetchRanges[agentUserId] = Array.isArray(rule.ranges) ? rule.ranges : [];
            this.entity.fetch[agentUserId] = typeof rule.distribution == 'string' ? rule.distribution : '';
            this.entity.fetchSelectionFields[agentUserId] = rule.fields && typeof rule.fields == 'object' ? { ...rule.fields } : {};
        },

        onParentFiltersUpdate(parentFilters) {
            this.lastParentFilters = parentFilters;
        },

        saveMaxRegistrations(infoReviewer){
            const timeoutName = "saveMaxRegistrations" + infoReviewer.id;
            const messages = useMessages();

            clearTimeout(this[timeoutName]);
            this[timeoutName] = setTimeout(async () => {
                this.entity.enableM
                await this.entity.invoke('setValuerMaxRegistrations',{relationId: infoReviewer.id, maxRegistrations: infoReviewer.metadata.maxRegistrations});
                messages.success(this.text('modificações salvas'));
            }, 3000)
        },

        parseRegistrationList(text) {
            if (!text || !text.trim()) {
                return null;
            }
            
            // Remove espaços extras e divide por vírgula, ponto e vírgula, espaço ou quebra de linha
            const separators = /[,;\s\n\r]+/;
            const numbers = text
                .split(separators)
                .map(num => num.trim())
                .filter(num => num.length > 0);
            
            return numbers.length > 0 ? numbers : null;
        },

        formatRegistrationList(registrationList) {
            if (!registrationList || !Array.isArray(registrationList) || registrationList.length == 0) {
                return '';
            }
            return registrationList.join(', ');
        },

        saveRegistrationList(infoReviewer) {
            const timeoutName = "saveRegistrationList" + infoReviewer.id;
            const messages = useMessages();

            clearTimeout(this[timeoutName]);
            this[timeoutName] = setTimeout(async () => {
                const registrationNumbers = this.parseRegistrationList(infoReviewer.registrationListText);
                await this.entity.invoke('setValuerRegistrationList', {
                    relationId: infoReviewer.id, 
                    registrationList: registrationNumbers
                });
                messages.success(this.text('modificações salvas'));
            }, 3000);
        },

        saveRegistrationListExclusive(infoReviewer) {
            const messages = useMessages();
            this.entity.invoke('setValuerRegistrationListExclusive', {
                relationId: infoReviewer.id, 
                exclusive: infoReviewer.metadata.registrationListExclusive
            }).then(() => {
                messages.success(this.text('modificações salvas'));
            });
        },

        toggleContent(reviewerId) {
            const reviewer = this.infosReviewers.find(r => r.id === reviewerId);
            if (reviewer) {
                reviewer.isContentVisible = !reviewer.isContentVisible;
            }
        },

        expandAllToggles() {
            const expand = !this.allExpanded;
            this.infosReviewers.forEach(reviewer => {
                reviewer.isContentVisible = expand;
            });
        },
    },
});