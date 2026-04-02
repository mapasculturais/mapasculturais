app.component('oc-actions', {
    template: $TEMPLATES['oc-actions'],
    emits: ['save'],
    setup() {
        const text = Utils.getTexts('oc-actions')
        const globalState = useGlobalState();
        const messages = useMessages();
        return { text, globalState, messages }
    },

    mounted() {
        window.addEventListener('useActions', this.changeUseActions);
    },
    props: {
        entity: {
            type: Entity,
            required: true
        },
        reloadTime: {
            type: [Boolean, Number],
            default: false
        },
        clearCache: {
            type: Boolean,
            default: false
        },
    },
    data() {
        let useActions = this.globalState.useActions === 'nouse-global' ? true : this.globalState.useActions;
        
        return {
            useActions: useActions
        }
    },
    methods: {
        changeUseActions(data) {
            this.useActions = data.detail.useActions;
        },
        save() {
            

            this.entity.save();
            this.$emit('save');

            if(this.reloadTime) {
                setTimeout(() => {
                    window.location.reload();
                }, this.reloadTime);
            }
        },
        async clearCacheExec() {
            try {
                const api = new API('settings');
                const url = Utils.createUrl('settings', 'clearCache');
                const res = await api.POST(url, {});

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }

                const responseData = await res.json();
                if (responseData) {
                    this.messages.success(this.text('clearCacheSuccess'));
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    this.messages.error(this.text('clearCacheError'));
                }
            } catch (e) {
                this.messages.error(this.text('clearCacheError'));
            }
        }
        
    }
});
