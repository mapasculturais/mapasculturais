app.component('opportunity-evaluations-list', {
    template: $TEMPLATES['opportunity-evaluations-list'],
    emits: ['toggle'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        textButton: {
            type: String,
            default: 'Button'
        },
        content: {
            type: String,
            default: 'Content'
        },
        userEvaluatorId: {
            type: String,
        }
    },

    setup() {
        const text = Utils.getTexts('opportunity-evaluations-list');
        return { text }
    },
    mounted() {
        this.getEvaluations();
        window.addEventListener('previousEvaluation', this.previousEvaluation);
        window.addEventListener('nextEvaluation', this.nextEvaluation);
    },
    data() {
        return {
            evaluations: [],
            isOpen: window.matchMedia('(min-width: 120rem)').matches ? true : false,
            pending: false,
            keywords: "",
            timeOut: null,
            roles: $MAPAS.currentUserRoles,
            filterKeyword: false,
            loading: false,
            onlyMe: true,
            filtersOptions: [
                {label: this.text('all'), value: 'all'},
                {label: this.text('pending'), value: 'pending'},
                {label: this.text('started'), value: '0'},
                {label: this.text('completed'), value: 1},
                {label: this.text('sent'), value: 2},
            ],
            filterStatus: $MAPAS.config.opportunityEvaluationsList?.evaluationStatusFilterCache || 'all'
        }
    },
    watch: {
        'pending'(_new, _old) {
            this.getEvaluations();
        },
        'filterStatus'(_new, _old) {
            this.getEvaluations();
        },
        'onlyMe' (_new, _old) {
            this.getEvaluations();
        }
    },
    methods: {
        colorByStatus(evaluation) {
            let result = 'pending';
            
            let eval = evaluation ? evaluation.status : null
            switch (eval) {
                case null:
                case "":
                case undefined:
                    result = 'pending'
                    break;
                case '0':
                case 0:
                    result = 'started'
                    break;
                case 1:
                    result = 'completed'
                    break;
                case 2:
                    result = 'sent'
                    break;
            }

            return result;
        },
        timeOutFind(delay = 1500) {
            clearTimeout(this.timeOut);

            this.timeOut = setTimeout(() => {
                this.getEvaluations();
            }, delay);
        },
        async getEvaluations() {

            this.loading = true;
            let args = {};
            args['@select'] = "id,owner.name";
            args['registration:@select'] = "id,owner.name,sentTimestamp";
            args['@opportunity'] = this.entity.opportunity.id;
            args['@evaluationId'] = `${this.userEvaluatorId}`

            if(this.keywords){
                args['registration:@keyword'] = this.keywords;
            }

            if (this.pending) {
                args['@pending'] = true;
            }

            if (this.filterStatus) {
                args['@filterStatus'] = this.filterStatus;
            }

            if (this.onlyMe) {
                args['@onlyMe'] = true;
            }
            
            if(this.entity.opportunity.avaliableEvaluationFields?.['agentsSummary']) {
                args['registration:@select']+= ',agentsData';
            }
            
            api = new API('opportunity');
            let url = api.createApiUrl('findEvaluations', args);

            await api.GET(url).then(response => response.json().then(objs => {
                this.evaluations = objs.map(function(item){
                    return {
                        agentsData: item.registration?.agentsData || [],
                        evaluationId: item.evaluation?.id,
                        registrationNumber: item.registration.number,
                        registrationId: item.registration.id,
                        registrationSentTimestamp: item.registration.sentTimestamp ? new McDate(item.registration.sentTimestamp.date) : null,
                        agentname: item.registration.owner?.name,
                        status: item?.evaluation?.status,
                        resultString: item?.evaluation?.resultString || null,
                        url: Utils.createUrl('registration', 'evaluation', [item.registration.id]),
                        valuer: item?.valuer
                    }
                });
                this.filterKeyword = false;
                this.evaluations.sort((a, b) => (a.registrationId - b.registrationId));
                window.dispatchEvent(new CustomEvent('evaluationRegistrationList', {detail:{evaluationRegistrationList:this.evaluations}}));

                this.loading = false;
            }));

            const globalState = useGlobalState();
            globalState.firstRegistration = this.evaluations[0];
            globalState.lastRegistration = this.evaluations[this.evaluations.length -1];
        },
        previousEvaluation(data) {
            this.goTo(data)
        },
        nextEvaluation(data) {
            this.goTo(data)
        },
        goTo(data) {
            var index = null;
            this.evaluations.forEach((obj, i) => {
                if (obj.registrationId === data.detail.registrationId) {
                    index = data.type === "nextEvaluation" ? i + 1 : i - 1;
                }
            });
            
            if (index >= 0 && index < this.evaluations.length) {
                var url = this.evaluations[index].url.href;
                window.location.href = url +`user:${this.userEvaluatorId}`;
            }

        },
        dateFormat(value) {
            const dateObj = new Date(value._date);
            return dateObj.toLocaleDateString("pt-BR");
        },
        emitToggle() {
            this.$emit('toggle');
        },
        stopPropagation(event) {
            if (this.viewLoad == "list") {
                event.stopPropagation();
            }
        },
        toggleMenu() {
            this.isOpen =  true;
        },
        showList(){
            result = true;
            if(this.roles.forEach(function(item){
                if(item.toLowerCase().match('admin')){
                    result = false;
                    return;
                }
            }));
            return result;
        },
        verifyState(evaluation) {
            switch (evaluation.resultString) {
                case 'Selecionado' :
                case 'Válida' :
                    return 'success__color';
                    
                case 'Inválida' : 
                case 'Não selecionado' : 

                    return 'danger__color';
                case 'Suplente' :
                    return 'warning__color';

                case null:
                default:
                    return '';
            }
        }
    },
});
