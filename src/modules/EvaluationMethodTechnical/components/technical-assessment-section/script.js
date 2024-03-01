
app.component('technical-assessment-section', {
    template: $TEMPLATES['technical-assessment-section'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const messages = useMessages();
        const text = Utils.getTexts('technical-assessment-section')
        return { text, messages }
    },

    data() {
        const api = new API();
        return {
            api,
            editingSections: [],
            timeout: null
        }
    },

    methods: {
        generateUniqueNumber() {
            return 's-' + Date.now() + Math.floor(Math.random() * 1000);
        },
        addSection() {
            let sectionId = 's-'+this.generateUniqueNumber();

            this.entity.sections.push(
                {
                    id: sectionId,
                    name: ''
                }
            );
            this.editingSections[sectionId] = true;
        },
        addCriteria(sid) {
            let sectionId = sid;
            this.entity.criteria.push({
                id: 'c-'+this.generateUniqueNumber(),
                sid: sectionId,
                title: '',
                min: 0,
                max: 10,
                weight: 1
            });
        },
        sendConfigs() {
            this.entity.save(3000);
        },
        editSections(sectionId) {
            this.editingSections[sectionId] = !this.editingSections[sectionId];
        },
        delSection(sectionId) {
            const criterias = this.entity.criteria.filter(criteria => criteria.sid !== sectionId);
            this.entity.criteria = criterias;
            this.entity.sections = this.entity.sections.filter(section => section.id !== sectionId);
            this.autoSave();
        },
        delCriteria(criteriaId) {
            this.entity.criteria = this.entity.criteria.filter(criteria => criteria.id !== criteriaId);
            this.autoSave();
        },
        autoSave() {
            this.entity.save(3000)
        }
    }
});
