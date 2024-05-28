app.component('entity-field', {
    template: $TEMPLATES['entity-field'],
    emits: ['change', 'save'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {         
        let uid = Math.random().toString(36).slice(2);
        let description, 
            value = this.entity[this.prop];

        description = this.entity.$PROPERTIES[this.prop] || {};
        
        if (description.type == 'array' && !(value instanceof Array)) {
            if (!value) {
                value = [];
            } else {
                value = [value];
            }
        }
        
        let isAdmin = function() {
            let result = false;
            $MAPAS.currentUserRoles.forEach(function(item){
                if(item.toLowerCase().match('admin')){
                    result = true;
                    return;
                }
            })

            return result;
        }

        if(this.entity.__objectType === "agent" && this.prop === "type" && !isAdmin()){
            
            var typeOptions = {};
            var optionsOrder = [];
            Object.keys(description.options).forEach(function(item, index){
                if(description.options[item] != "Individual"){
                    typeOptions[index] = description.options[item];
                    optionsOrder.push(parseInt(index));
                }
            });
            description.options = typeOptions;
            description.optionsOrder = optionsOrder;
        }

        let fieldType = this.type || description.field_type || description.type;

        if(this.type == 'textarea' || (description.type == 'text' && description.field_type === undefined)) {
            fieldType = 'textarea';
        }

        return {
            __timeout: null,
            description: description,
            propId: `${this.entity.__objectId}--${this.prop}--${uid}`,
            fieldType,
            currencyValue: this.entity[this.prop],
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
        placeholder: {
            type: String
        },
        type: {
            type: String,
            default: null
        },
        hideLabel: {
            type: Boolean,
            default: false
        },
        hideDescription: {
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
        },
        disabled: {
            type: Boolean,
            default: false
        },
    },

    computed: {
      
        charRemaining() {
            return 400 - this.value.length;
        },
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
        propExists(){
            return !! this.entity.$PROPERTIES[this.prop];
        },

        change(event, now) {
            clearTimeout(this.__timeout);
            let oldValue = this.entity[this.prop] ? JSON.parse(JSON.stringify(this.entity[this.prop])) : null;
            
            this.__timeout = setTimeout(() => {
               if(this.is('date') || this.is('datetime') || this.is('time')) {
                    if(event) {
                        this.entity[this.prop] = new McDate(event);
                    } else {
                        this.entity[this.prop] = '';
                    }

                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event});
                } else if(this.is('currency')) {
                    this.entity[this.prop] = this.currencyValue;
                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event.target.checked});
                } else if(this.is('checkbox')) {
                    this.entity[this.prop] = event.target.checked;
                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event.target.checked});
                } else if (this.is('multiselect')) {
                    if (this.entity[this.prop] === '' || !this.entity[this.prop]) {
                        this.entity[this.prop] = []
                    } else if (typeof this.entity[this.prop] !== 'object') {
                        this.entity[this.prop] = this.entity[this.prop].split(";");
                    }

                    let index = this.entity[this.prop].indexOf(event.target.value);
                    if (index >= 0) {
                        this.entity[this.prop].splice(index, 1);
                    } else {
                        this.entity[this.prop].push(event.target.value)
                    }

                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event.target.value});
                } else {
                    this.entity[this.prop] = event.target.value;
                    this.$emit('change', {entity: this.entity, prop: this.prop, oldValue: oldValue, newValue: event.target.value});
                }

                if (this.autosave && (now || JSON.stringify(this.entity[this.prop]) != JSON.stringify(oldValue))) {
                    this.entity.save(now ? 0 : this.autosave).then(() => {
                        this.$emit('save', this.entity);
                    });
                }

            }, now ? 0 : this.debounce);
        },

        is(type) {
            return this.fieldType == type;
        }
    },
});