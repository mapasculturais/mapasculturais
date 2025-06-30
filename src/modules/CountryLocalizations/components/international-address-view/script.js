
app.component('international-address-view', {
    template: $TEMPLATES['international-address-view'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },
    data(){
        return {};
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: false,
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
            
            let result = this.entity.publicLocation;

            return result;
        },
    }
});
