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
            filterKeyword: false
        }
    },
    watch: {
        'pending'(_new, _old) {
            this.timeOutFind(_new, _old);
        }
    },
    methods: {
        timeOutFind(_new, _old) {
            if (_new != _old) {
                clearTimeout(this.timeOut);
                this.timeOut = setTimeout(() => {
                    this.getEvaluations();
                }, 1500);
            }
        },
        filterKeywordExec(_new, _old) {
            if(!this.keywords){
                messages.error(this.text('Informe a palavra chave'));
            }else{
                this.getEvaluations();
            }
        },
        async getEvaluations() {
            let args = {};
            args['@select'] = "id,owner.name";
            args['@opportunity'] = this.entity.opportunity.id;

            if(this.keywords){
                args['registration:@keyword'] = this.keywords;
            }

            if (this.pending) {
                args['@pending'] = true;
            }

            api = new API('opportunity');
            let url = api.createApiUrl('findEvaluations', args);

            await api.GET(url).then(response => response.json().then(objs => {
                this.evaluations = objs.map(function(item){
                    return {
                        registrationid:item.registration.id,
                        agentname: item.registration.owner?.name,
                        status: item?.evaluation?.status,
                        resultString: item?.evaluation?.resultString || null,
                        url: Utils.createUrl('registration', 'evaluation', [item.registration.id])
                    }
                });
                this.filterKeyword = false;
                this.evaluations.sort((a, b) => (a.registrationid - b.registrationid));
                window.dispatchEvent(new CustomEvent('evaluationRegistrationList', {detail:{evaluationRegistrationList:this.evaluations}}));
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
                if (obj.registrationid === data.detail.registrationId) {
                    index = data.type === "nextEvaluation" ? i + 1 : i - 1;
                }
            });

            if (index >= 0 && index < this.evaluations.length) {
                var url = this.evaluations[index].url.href;
                window.location.href = url;
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
