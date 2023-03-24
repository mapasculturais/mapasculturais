app.component('entity-field', {
    template: $TEMPLATES['entity-field'],
    emits: ['change', 'save'],

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
            return {};
        }
        
        if (description.type == 'array' && !(value instanceof Array)) {
            if (!value) {
                value = [];
            } else {
                value = [value];
            }
        }

        let fieldType = this.type || description.field_type || description.type;

        if(this.type == 'textarea' || (description.type == 'text' && description.field_type === undefined)) {
            fieldType = 'textarea';
        }

        return {
            __timeout: null,
            description: description,
            propId: `${this.entity.__objectId}--${this.prop}--${uid}`,
            fieldType
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
            type: [ Number, String, Date ],
            default: 0 || null
        },
        max: {
            type: [ Number, String, Date ],
            default: 0 || null
        },
        fieldDescription: {
            type: String,
            default: null
        },
        autosave: {
            type: Number,
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
            return this.entity[this.prop]?.id ?? this.entity[this.prop];
        }
    },
    
    methods: {
        change(event) {

            clearTimeout(this.__timeout);

            let oldValue = this.entity[this.prop];

            this.__timeout = setTimeout(() => {
                if(this.is('date') || this.is('datetime') || this.is('time')) {
                    this.entity[this.prop] = new McDate(event);
                } else {
                    this.entity[this.prop] = event.target.value;
                }

                if(this.is('date') || this.is('datetime') || this.is('time')) {
                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event});
                } else {
                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event.target.value});
                }

                if (this.autosave && this.entity[this.prop] != oldValue) {
                    clearTimeout(this.entity.__autosaveTimeout);
                    this.entity.__autosaveTimeout = setTimeout(() => {
                        this.entity.save();
                        this.$emit('save', this.entity);
                    }, this.autosave);
                }
            }, this.debounce);
        },

        is(type) {
            return this.fieldType == type;
        }
    },
});