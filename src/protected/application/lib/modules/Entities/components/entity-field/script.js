app.component('entity-field', {
    template: $TEMPLATES['entity-field'],
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
            description = this.entity.$PROPERTIES[this.prop];
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

        // if ((description.type === 'smallint' || description.type === 'integer' || description.type === 'number') && !(value instanceof Number)) {
        //     description.min = this.props.min;
        //     description.max = this.props.max;
        // }

        return {
            __timeout: null,
            description: description,
            propId: `${this.entity.__objectId}--${this.prop}--${uid}`,
            fieldType: this.type || description.input_type || description.type
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
        hideLabel: {
            type: Boolean,
            default: false
        },
        hideRequired: {
            type: Boolean,
            default: false
        },
        debounce: {
            type: Number,
            default: 0
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        min: {
            type: Number,
            default: 0
        },
        max: {
            type: Number,
            default: 0
        },
        fieldDescription: {
            type: String,
            default: null
        },
        maskType: {
            type: String,
            default: null
        }
    },

    computed: {
        hasErrors() {
            let errors = this.entity.__validationErrors[this.prop] || [];
            if(errors.length > 0){
                return true;
            } else {
                return false;
            }
        },
        errors() {
            return this.entity.__validationErrors[this.prop];
        },
        value() {
            return this.entity[this.prop];
        },
        isMask () {
            return this.maskType !== null
        },
        valueMasked () {
            return this.mask(this.maskType)
        }
    },
    
    methods: {
        change(event) {
            clearTimeout(this.__timeout);

            let oldValue = this.entity[this.prop];

            this.__timeout = setTimeout(() => {
                if(this.is('date')) {
                    this.entity[this.prop] = new McDate(event.target.value);
                } else {
                    this.entity[this.prop] = event.target.value;
                }

                if(this.isMask) {
                    const valueWithoutMask = event.target.value.replace(/\./g, '').replace(/-/g, '').replace(/\(/g, '').replace(/\)/g, '');
                    const oldValueWithoutMask = oldValue.replace(/\./g, '').replace(/-/g, '').replace(/\(/g, '').replace(/\)/g, '');
                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValueWithoutMask, newValue: valueWithoutMask});
                } else {
                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event.target.value});
                }

            }, this.debounce);
        },

        is(type) {
            return this.fieldType == type;
        },

        removeMask (value) {
          return value.replace(/\./g, '').replace(/-/g, '').replace(/\(/g, '').replace(/\)/g, '');
        },

        mask (type) {
            let value = this.entity[this.prop];
            value = this.removeMask(value);
            const regexInt = /^-?[0-9]+$/;
            if(type == 'cpf') {
                if(value.length > 11) {
                    value = value.slice(0, 11);
                }
                if (regexInt.test(value)) {
                    return value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/g, '$1.$2.$3-$4');
                }
                return value
            } else if(type == 'cnpj') {
                if (regexInt.test(value)) {
                    return value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/g, '$1.$2.$3/$4-$5');
                }
                return value
            } else if(type == 'telephone') {
                if (regexInt.test(value)) {
                    return value.replace(/(\d{2})(\d{5})(\d{4})/g, '($1) $2-$3');
                }
                return value
            } else if(type == 'cep') {
                if(regexInt.test(value)) {
                    return value.replace(/\D/g, '').replace(/^(\d{5})(\d{3})+?$/, '$1-$2');
                }
                return value;
            }
        }
    },
});