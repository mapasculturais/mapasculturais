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
        }
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
});
