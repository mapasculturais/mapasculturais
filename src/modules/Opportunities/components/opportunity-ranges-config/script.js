app.component('opportunity-ranges-config', {
    template: $TEMPLATES['opportunity-ranges-config'],
    setup() {
        const messages = useMessages();
        return{messages};
    },
    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    data() {
        return {
            timeout: null
        }
    },
    created() {
        this.entity.registrationRanges = this.entity.registrationRanges || [];
    },
    methods: { 
        addRange() {
            if (this.areAllRangesValid()) {
                this.entity.registrationRanges.push({
                    label: '',
                    limit: 0,
                    value: NaN
                });
            }else{
               this.messages.error("Por favor, preencha todos os campos da faixa antes de adicionar uma nova faixa.");
            }
        },
        removeRange(item) {
            this.entity.registrationRanges = this.entity.registrationRanges.filter(function(value, key) {
                return item != key;
            });
            this.autoSave();
        },
        autoSaveRange(item) {
            if(item.label.length > 0) {
                this.autoSave();
            }
        },
        autoSave() {
            if (this.areAllRangesValid()) {
                this.entity.save(3000);
            }
        },
        areAllRangesValid() {
            return this.entity.registrationRanges.every(range => range.label.trim().length > 0);
        },
    }
});
