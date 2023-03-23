app.component('registration-steps', {
    template: $TEMPLATES['registration-steps'],

    props: {

    },

    setup() {
        const text = Utils.getTexts('registration-steps')
        return { text }
    },

    mounted() {
        const iframe = document.getElementById('registration-form');
        const globalState = useGlobalState();

        const self = this;

        window.addEventListener("message", (event) => {    
            if (event.data.type == "section.tops") {
                
                const iftop = iframe.offsetTop;
                const scrollY = window.scrollY;
                const sections = event.data.data;
                let currentLabel = this.text('Informações básicas');
                for(let section of sections) {
                    if(!self.sectionsByName[section.label]) {
                        continue;
                    }
                    self.sectionsByName[section.label].top = section.top + iftop;
                    if(scrollY > section.top + iftop + 400) {
                        currentLabel = section.label;
                    }
                    if(window.scrollY > document.body.offsetHeight - window.innerHeight - 100) {
                        currentLabel = section.label;
                    }
                }

                globalState['stepper'] = self.sections.indexOf(currentLabel);
            }
        })
    },

    data() {
        let sectionsByName = {};
        let sections = [ 
            this.text('Informações básicas'),
        ];

        for (let entry of $MAPAS.registrationFields) {
            if (entry.fieldType == 'section') {
                if(!sectionsByName[entry.title]) {
                    sections.push(entry.title);
                    sectionsByName[entry.title] = entry;
                }
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
                window.scroll(0,0);
            } else {
                window.scroll(0,this.sectionsByName[event].top + 500);
            }
        },


    },
});
