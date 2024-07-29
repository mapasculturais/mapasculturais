app.component('select-entity', {
    template: $TEMPLATES['select-entity'],
    emits: ['select', 'fetch'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('select-entity')
        return { text }
    },

    data() {
        return {
            itensText: '',
            buttonText: '',
            placeholder: '',
            keyword: '',
            close: false
        }
    },

    created() {
        switch (this.type) {
            case 'agent':
                this.itensText = this.text('Selecione um dos agentes');
                this.buttonText = this.text('Crie um novo agente');
                this.placeholder = this.text('Pesquise por agentes');
                break;
            case 'space':
                this.itensText = this.text('Selecione um dos espaços');
                this.buttonText = this.text('Crie um novo espaço');
                this.placeholder = this.text('Pesquise por espaços');
                break;
            case 'event':
                this.itensText = this.text('Selecione um dos eventos');
                this.buttonText = this.text('Crie um novo evento');
                this.placeholder = this.text('Pesquise por eventos');
                break;
            case 'project':
                this.itensText = this.text('Selecione um dos projetos');
                this.buttonText = this.text('Crie um novo projeto');
                this.placeholder = this.text('Pesquise por projetos');
                break;
            case 'opportunity':
                this.itensText = this.text('Selecione uma das oportunidades');
                this.buttonText = this.text('Crie uma nova oportunidade');
                this.placeholder = this.text('Pesquise por oportunidades');
                break;
        }
    },

    props: {
        type: {
            type: String,
            required: true            
        },
        select: {
            type: String,
            default: 'id,name,files.avatar'
        },
        query: {
            type: Object,
            default: {}
        },
        permissions: {
            type: String,
            default: "@control"
        },
        limit: {
            type: Number,
            default: 25
        },
        createNew: {
            type: Boolean,
            default: false
        },
        scope: {
            type: String
        },
        openside: {
            type: String
        },
        buttonLabel: {
            type: String,
            default: ''
        },
        buttonClasses: {
            type: String,
            default: ''
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },
    
    methods: {
        selectEntity(entity, close) {
            this.$emit('select', entity);
            close();
        },
        
        fetch(entities) {
            this.$emit('fetch', entities);
        },

        clearField() {
            delete this.query['@keyword'];
        },        
    },
});
