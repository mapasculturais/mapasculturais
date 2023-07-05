app.component('registration-evaluation-info', {
    template: $TEMPLATES['registration-evaluation-info'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },
    
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-evaluation-info')
        return { text }
    },

    data() {
        let activeItems = [];
        return {
            infos: $MAPAS.evaluationInfos,
            activeItems,
        }
    },

    computed: {
    },
    
    methods: {
        open(index) {
            this.activeItems[index] = true;
        },

        close(index) {
            delete this.activeItems[index];
        },

        toggle(index) {
            if (this.activeItems[index]) {
                this.close(index);
            } else {
                this.open(index);
            }
        }
    },
});
