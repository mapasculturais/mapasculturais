app.component('mc-side-menu', {
    template: $TEMPLATES['mc-side-menu'],
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
        const text = Utils.getTexts('mc-side-menu');
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
            isOpen: false,
            pending: false,
            keywords: "",
            timeOut: null,
        }
    },
    watch: {
        'keywords'(_new, _old) {
            this.timeOutFind(_new, _old);
        },
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
        async getEvaluations() {
            let args = {};
            args['@select'] = "id,singleUrl,category,owner.{id,name,singleUrl},consolidatedResult,evaluationResultString,status";
            args['@opportunity'] = this.entity.opportunity.id;
            args['@keyword'] = 'like(' + this.keywords + ')';

            if (this.pending) {
                args['@pending'] = true;
            }

            api = new API('opportunity');
            let url = api.createApiUrl('findRegistrationsAndEvaluations', args);

            await api.GET(url).then(response => response.json().then(objs => {
                this.evaluations = objs.map(function (item) {
                    item.url = Utils.createUrl('registration', 'evaluation', [item.registrationid]);;
                    return item;
                });
                this.evaluations.sort((a, b) => (a.registrationid - b.registrationid));
            }));
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
            this.isOpen = this.isOpen ? false : true;
        },
    },
});
