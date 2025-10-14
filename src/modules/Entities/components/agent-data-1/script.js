app.component('agent-data-1', {
    template: $TEMPLATES['agent-data-1'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('agent-data-1')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        showOnlyPublicData: {
            type: Boolean,
            default: false,
        },

        alwaysShowTitle: {
            type: Boolean,
            default: true,
        },
    },
    methods: {
        canShow(objt) {
            if(objt <= 0){
                return false;
            }
            
            if(objt.length == 1 && objt[0] == ''){
                 return false;
            }else{
                return true;
            }

        },
        getTextI18n(str) {
            return this.text(str)
        },
        verifyAllFields(fields) {
            let empty = true;

            for (let fieldName of fields) {
                let field = this.entity[fieldName]
                if (field !== undefined && field !== null) {
                    if (field instanceof Array) {
                        if (field.length) {
                            empty = false;
                        }
                    }
                    else {
                        empty = false;
                    }
                }
            }
            return !empty;
        },
        
        verifySensitiveFields() {
            return this.verifyAllFields($MAPAS.config['agent-data-1'].sensitiveFields)
        },
        verifyFields () {
            return this.verifyAllFields($MAPAS.config['agent-data-1'].fields)

        },
    },
});
