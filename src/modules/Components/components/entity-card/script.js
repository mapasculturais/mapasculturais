app.component('entity-card', {
    template: $TEMPLATES['entity-card'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];

        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-card')
        return { text, hasSlot }
    },

    data() {
        return {}
    },

    props: {
        class: {
            type: [String, Object, Array],
            default: ''
        },
        entity: {
            type: Entity,
            required: true
        },
        portrait: {
            type: Boolean,
            default: false
        },
        sliceDescription: {
            type: Boolean,
            default: false,
        },
        tag: {
            type: String,
            default: 'h2',
        },
    },

    computed: {
        classes() {
            return [this.class, {'portrait': this.portrait}]
        },
        showShortDescription() {
            if (this.entity.shortDescription) {
                if (this.entity.shortDescription.length > 400) {
                    return this.entity.shortDescription.substring(0, 400) + '...';
                } else {
                    return this.entity.shortDescription;
                }
            }
        },
        seals() {
            return (this.entity.seals.length > 0 ? this.entity.seals.slice(0, 2) : false);
        },
        areas() {
            return (Array.isArray(this.entity.terms.area) ? this.entity.terms.area.join(", ") : false);
        },
        tags() {
            return (Array.isArray(this.entity.terms.tag) ? this.entity.terms.tag.join(", ") : false);
        },
        linguagens() {
            return (Array.isArray(this.entity.terms.linguagem) ? this.entity.terms.linguagem.join(", ") : false);
        },
        openSubscriptions() {
            if (this.entity.__objectType == "opportunity") {
                if (this.entity.registrationFrom && this.entity.registrationTo) {
                    return this.entity.registrationFrom.isPast() && this.entity.registrationTo.isFuture();
                } else {
                    return false;
                }
            }
            return false;
        },
        useLabels() {
            return this.openSubscriptions || this.hasSlot('labels')
        }
    },
    methods: {
        slice(text, qtdChars) {
            if (text && text.length > qtdChars) {
                let slicedText = text.slice(0, qtdChars);

                let _text = text.split(' '); 
                let _slicedText = slicedText.split(' ');

                let _textLastWord = _text[_slicedText.length - 1];
                let _slicedTextLastWord = _slicedText[_slicedText.length - 1];

                /* se palavra for cortada, remove */
                if (_slicedTextLastWord  !== _textLastWord ) {
                    _slicedText.pop();
                    _textLastWord = _slicedText.at(-1);
                };

                /* verifica pontuações ao final da ultima palavra */
                let especialChars = ['.', ',', '!', '?'];
                especialChars.forEach(function(symbol) {
                    if (typeof _textLastWord == 'string' && _textLastWord.indexOf(symbol) !== -1) {
                        _slicedText[_slicedText.indexOf(_textLastWord)] = _textLastWord.slice(0, -1);
                    };
                });

                return _slicedText.join(' ') + '...';
            }
            return text;
        },
    },
});
