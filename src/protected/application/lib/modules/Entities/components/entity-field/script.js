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
        mask: {
            type: Boolean,
            default: false
        },
        minDate: {
            type: Date,
            default: null
        },
        maxDate: {
            type: Date,
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

                this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event.target.value});
            }, this.debounce);
        },

        is(type) {
            return this.fieldType == type;
        }
    },
});