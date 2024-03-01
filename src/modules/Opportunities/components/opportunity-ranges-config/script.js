app.component('opportunity-ranges-config', {
    template: $TEMPLATES['opportunity-ranges-config'],
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
            this.entity.registrationRanges.push({
                label: '',
                limit: 0,
                value: NaN
            });
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
            this.entity.save(3000)
        }
    }
});
