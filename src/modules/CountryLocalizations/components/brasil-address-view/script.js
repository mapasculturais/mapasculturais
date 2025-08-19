
app.component('brasil-address-view', {
    template: $TEMPLATES['brasil-address-view'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },
    data(){
        return {};
    },

    computed: {
        hasPublicLocation() {
            return !!this.entity.$PROPERTIES.publicLocation;
        },
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        hideLabel: {
            type: Boolean,
            default: false,
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    methods: {
        verifiedAdress() {
            if(this.entity.currentUserPermissions['@control']){
                return true;
            };
            const fields = ['En_Nome_Logradouro', 'En_Num', 'En_Bairro', 'En_Municipio', 'En_Estado', 'En_CEP'];
            let result = !this.hasPublicLocation || this.entity.publicLocation;
            fields.forEach((element)=> {
                if(this.entity[element]==null) {
                    result = false;
                    return;
                }
            });
               
            return result;
        },
        showAddress() {
            if(!this.entity.address && !this.entity.endereco) {
                return false;
            }
            
            return this.entity.address || this.entity.endereco;
        },
    }
});
