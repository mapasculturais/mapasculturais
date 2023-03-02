/**
 * Vue Lifecycle
 * 1. setup
 * 2. beforeCreate
 * 3. created
 * 4. beforeMount
 * 5. mounted
 * 
 * // sempre que há modificação nos dados
 *  - beforeUpdate
 *  - updated
 * 
 * 6. beforeUnmount
 * 7. unmounted                  
 */

app.component('__template__', {
    template: $TEMPLATES['__template__'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        textoLocalizado: {
            type: String,

            // nas propriedades não é possível utilizar o this.text
            default: __('texto localizado', '__template__') 
        },

        name: {
            type: String,
            required: true,
            validator: (value) => {
                return value.length > 3;
            }
        },

        lastname: {
            type: String
        },

        nickname: {
            type: String
        },

    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('__template__')
        return { text, hasSlot }
    },

    beforeCreate() { },
    created() { },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    data() {
        return {
            displayName: this.nickname || this.name,
            fullname: this.fullname ? `${this.name} ${this.lastname}` : `${this.name}`,
            localizedData: this.text('texto localizado')
        }
    },

    computed: {
        compareDisplayName() {
            return this.entity.name == this.displayName;
        },

        compareFullname() {
            return this.entity.nomeCompleto == this.fullname;
        }
    },
    
    methods: {
        defineNames () {
            this.entity.name = this.displayName;
            this.entity.nomeCompleto = this.fullname;

            // emite o evento enviando o data
            this.$emit('namesDefined', this.entity);
        }
    },
});
