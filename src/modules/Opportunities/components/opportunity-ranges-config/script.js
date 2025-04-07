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

                this.$nextTick(() => {
                    const lastIndex = this.entity.registrationRanges.length - 1;
                    const descriptionInput = this.$refs['description-' + lastIndex];
                    if (descriptionInput && descriptionInput.length > 0) {
                        descriptionInput[0].focus();
                    }
                });
            } else{
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
            item.label = item.label.trim();
            if(item.label.length > 0) {
                this.autoSave();
            } 
            else { 
                const index = this.entity.registrationRanges.indexOf(item);
                if (index !== -1) {
                    this.removeRange(index);
                }
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
