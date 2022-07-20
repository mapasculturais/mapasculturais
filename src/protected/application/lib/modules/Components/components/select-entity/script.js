app.component('select-entity', {
    template: $TEMPLATES['select-entity'],
    emits: ['select'],

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
            icon: '',
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
                this.icon = 'fa-solid:user-friends';
                break;
            case 'space':
                this.itensText = this.text('Selecione um dos espaços');
                this.buttonText = this.text('Crie um novo espaço');
                this.placeholder = this.text('Pesquise por espaços');
                this.icon = 'clarity:building-line';
                break;
            case 'event':
                this.itensText = this.text('Selecione um dos eventos');
                this.buttonText = this.text('Crie um novo evento');
                this.placeholder = this.text('Pesquise por eventos');
                this.icon = 'ant-design:calendar-twotone';
                break;
            case 'project':
                this.itensText = this.text('Selecione um dos projetos');
                this.buttonText = this.text('Crie um novo projeto');
                this.placeholder = this.text('Pesquise por projetos');
                this.icon = 'ri:file-list-2-line';
                break;
            case 'opportunity':
                this.itensText = this.text('Selecione uma das oportunidades');
                this.buttonText = this.text('Crie uma nova oportunidade');
                this.placeholder = this.text('Pesquise por oportunidades');
                this.icon = 'icons8:idea';
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
            default: {"@permissions": "@control"}
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
    },
    
    methods: {

        selectEntity(entity, close) {
            this.$emit('select', entity);
            close();
        }

        
    },
});
