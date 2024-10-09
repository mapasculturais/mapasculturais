app.component('registration-status', {
    template: $TEMPLATES['registration-status'],

    props: {
        registration: {
            type: Entity,
            required: true
        },

        phase: {
            type: Entity,
            required: true
        }
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('registration-status');
        return { text, hasSlot }
    },

    methods: {
		formatNote(note) {
			note = parseFloat(note);
			return note.toLocaleString($MAPAS.config.locale);
		},
		verifyState(registration) {
            switch (registration.status) {
                case 10:
                    return 'has-color is-success';

                case 2 :
                case 0 :

                    return 'has-color is-danger';
				case 3 :
				case 8 :
                    return 'has-color is-warning';

                case null:
                default:
                    return '';
            }
        }
    }
});
