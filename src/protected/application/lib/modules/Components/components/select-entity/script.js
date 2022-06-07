app.component('select-entity', {
    template: $TEMPLATES['select-entity'],
    emits: ['select'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
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
                this.itensText = 'Selecione um de seus agentes';
                this.buttonText = 'Crie um novo agente';
                this.placeholder = 'Pesquise por agentes';
                this.icon = 'fa-solid:user-friends';
                break;
            case 'space':
                this.itensText = 'Selecione um de seus espaços';
                this.buttonText = 'Crie um novo espaço';
                this.placeholder = 'Pesquise por espaços';
                this.icon = 'clarity:building-line';
                break;
            case 'event':
                this.itensText = 'Selecione um de seus eventos';
                this.buttonText = 'Crie um novo evento';
                this.placeholder = 'Pesquise por eventos';
                this.icon = 'ant-design:calendar-twotone';
                break;
            case 'project':
                this.itensText = 'Selecione um de seus projetos';
                this.buttonText = 'Crie um novo projeto';
                this.placeholder = 'Pesquise por projetos';
                this.icon = 'ri:file-list-2-line';
                break;
            case 'opportunity':
                this.itensText = 'Selecione uma de suas oportunidades';
                this.buttonText = 'Crie uma nova oportunidade';
                this.placeholder = 'Pesquise por oportunidades';
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
        }
    },
    
    methods: {

        selectEntity(entity, close) {
            this.$emit('select', entity);
            close();
        }

        
    },
});
