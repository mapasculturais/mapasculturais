app.component('system-roles--modal', {
    template: $TEMPLATES['system-roles--modal'],
    emits: ['created'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('system-roles--modal')
        return { text }
    },

    data() {
        return {
            instance: null,
            permissions: $MAPAS.EntityPermissionsList || [],
            saveLabel: this.entity ? this.text('atualizar função') : this.text('Adicionar'),
            title: this.entity ? this.text('editar função') + ' ' + this.entity.name : this.text('criar nova função de usuário')
        }
    },

    props: {
        list: String,
        entity: Entity
    },
    
    methods: {
        createInstance() {
            this.instance = this.entity || Vue.ref(new Entity('system-role'));
            if (!this.entity) {
                this.instance.permissions = [];
            }
        },

        destroyInstance() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => this.instance = null, 200);
        },

        save (modal) {
            const lists = useEntitiesLists();
            modal.loading(true);
            
            this.instance.save().then((response) => {
                const list = lists.fetch(this.list);
                list.push(response);
                modal.close();
            }).catch((e) => {
                modal.loading(false);
            });
        },

        resetInstance() {
            this.instance = this.createInstance();
        }
    },
});
