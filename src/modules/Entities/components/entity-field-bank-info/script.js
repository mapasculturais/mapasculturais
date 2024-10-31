app.component('entity-field-bank-info', {
    template: $TEMPLATES['entity-field-bank-info'],
    emits: ['change'],

    props: {
        entity: {
            type: Object,
            required: true
        },
        fieldName: {
            type: String,
            required: true
        },
    },

    data() {
        return {
            bankFields: this.skeleton(),
            accountTypes: $MAPAS.config.entityFieldConfig.accountTypes || {},
            bankTypes: $MAPAS.config.entityFieldConfig.bankTypes || {},
            errors: {}
        }
    },

    created() {
        this.bankFields = this.entity[this.fieldName] ?? this.skeleton();
    },


    methods: {
        change(){
            this.$emit("change",this.bankFields);
        },
        skeleton() {
            return {
                account_type: '',
                number: '',
                branch: '',
                dv_branch: '',
                account_number: '',
                dv_account_number: '',
            }
        }
    }
});