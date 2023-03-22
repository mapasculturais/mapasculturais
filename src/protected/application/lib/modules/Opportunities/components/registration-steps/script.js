app.component('registration-steps', {
    template: $TEMPLATES['registration-steps'],

    props: {

    },

    setup() {
        const text = Utils.getTexts('registration-steps')
        return { text }
    },

    data() {
        let sectionsByName = {};
        let sections = [ 
            this.text('Informações básicas'),
        ];

        for (let entry of $MAPAS.registrationFields) {
            if (entry.fieldType == 'section') {
                sections.push(entry.title);
                sectionsByName[entry.title] = entry
            }
        }

        return {
            sections,
            sectionsByName,
        }
    },
    
    methods: {
        goToSection(event) {
            const iframe = document.getElementById('registration-form');
            if (event == this.text('Informações básicas')) {
                window.location.hash = 'main-info';
                iframe.contentDocument.location.hash = '';
            } else {
                history.replaceState(null, null, ' ');
                iframe.contentDocument.location.hash = this.sectionsByName[event].fieldName;
            }
        },
    },
});
