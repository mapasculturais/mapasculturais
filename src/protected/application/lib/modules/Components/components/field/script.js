app.component('field', {
    template: $TEMPLATES['field'],
    emits: ['change'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {
        
    },

    data() {
        let uid = Math.random().toString(36).slice(2);
        let description, 
            value = this.entity[this.prop];
        try{
             description = this.entity.__properties[this.prop];
        } catch (e) {
            console.log(`Propriedade ${this.prop} não existe na entidade`);
            return;
        }
        
        if (description.type == 'array' && !(value instanceof Array)) {
            if (!value) {
                value = [];
            } else {
                value = [value];
            }
        }
        
        return {
            __timeout: null,
            description: description,
            propId: `${this.entity.__objectId}--${this.prop}--${uid}`,
            fieldType: this.type || description.input_type || description.type,
            value: value,
            errors: []
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        prop: {
            type: String,
            required: true
        },
        label: {
            type: String,
            default: null
        },
        type: {
            type: String,
            default: null
        },
        showLabel: {
            type: Boolean,
            default: true
        },
        debounce: {
            type: Number,
            default: 2000
        }
    },
    
    methods: {
        hasErrors() {
            let errors = this.entity.__validationErrors[this.prop] || [];
            if(errors.length > 0){
                return true;
            } else {
                return false;
            }
        },

        getErrors() {
            return this.entity.__validationErrors[this.prop];
        },

        change() {
            clearTimeout(this.__timeout);

            let oldValue = this.entity[this.prop];

            this.__timeout = setTimeout(() => {
                this.entity[this.prop] = this.value;

                this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: this.value});
            }, this.debounce);
        },

        is(type) {
            return this.fieldType == type;
        }
    },
});