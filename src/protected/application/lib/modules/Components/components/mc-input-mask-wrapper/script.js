app.component('mc-input-mask-wrapper', {
    template: $TEMPLATES['mc-input-mask-wrapper'],
    emits: ['change'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        label: {
            type: String,
            required: false
        },

        prop: {
            type: String,
            required: true
        }
    },

    created () {
      this.model = this.entity[this.prop]
    },

    data () {
        return {
            MASK_OPTIONS: {
                cpf: '###.###.###-##',
                cnpj: '##.###.###/####-##',
                telefone: '(##) #####-####'
            },
            model: ''
        };
    },

    computed: {
        dataMask () {
            const optionsSelected = this.MASK_OPTIONS[this.prop];
            if(optionsSelected) {
                return optionsSelected;
            } else {
                if(this.prop.includes('telefone')) {
                    return this.MASK_OPTIONS.telefone;
                }
            }
            return '';
        }
    },

    methods: {
        is(val) {
            return val === this.prop;
        },
        input (value) {
            if(value && value.detail && value.detail.unmasked) {
                this.entity[this.prop] = value.detail.unmasked;
            }
        },
    }
});
