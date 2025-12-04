app.component('entity-links', {
    template: $TEMPLATES['entity-links'],
    emits: [],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-links')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: 'Links'
        },
        editable: {
            type: Boolean,
            default: false
        },
        classes: {
            type: String,
            default: '',
        },
        hideTitle: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            metalist: {
                title: '',
                value: ''
            },
            newLinks: [],
        }
    },

    methods: {
        async save(link, index) {
            console.log(link);
            if (!link.value || !link.title) {
                const messages = useMessages();
                messages.error(this.text('preencha todos os campos'));
                return;
            }
            await this.entity.createMetalist('links', link);
            this.remove(index);
        },

        async update(metalist) {
            if (!metalist.newData.title || !metalist.newData.value) {
                const messages = useMessages();
                messages.error(this.text('preencha todos os campos'));
                return;
            }
            metalist.title = metalist.newData.title;
            metalist.value = metalist.newData.value;

            await metalist.save();
        },

        remove (index){
            this.newLinks.splice(index, 1);
        },

        addLink() {
            this.newLinks.push({
                title: '',
                value: ''
            });
        },

        async create(popover, close) {
            if (!this.metalist.value || !this.metalist.title) {
                const messages = useMessages();
                messages.error(this.text('preencha todos os campos'));
                return;
            }
            await this.entity.createMetalist('links', this.metalist);
            this.metalist = { title: '', value: '' };
            if (close) close();
        }
    }

});