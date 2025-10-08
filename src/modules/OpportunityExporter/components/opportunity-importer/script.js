app.component('opportunity-importer', {
    template: $TEMPLATES['opportunity-importer'],
    
    emits: ['imported'],
    
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-importer')
        return {text }
    },

    data() {
        return {
            availableFilters: this.createFilters(),
            filters: this.createFilters(),
            opportunity: null,
        }
    },

    watch: {
        opportunity () {
            this.filters = this.createFilters()
            this.availableFilters = this.createFilters()

            if (!this.opportunity) {
                return
            }

            for (const [key, value] of Object.entries(this.opportunity)) {
                if (this.isEmpty(value)) {
                    this.filters[key] = false
                    this.availableFilters[key] = false
                }
            }
        }
    },
    
    methods: {
        cancelImport (modal) {
            modal.close()
            this.opportunity = null
        },

        createFilters () {
            return {
                infos: true,
                files: true,
                images: true,
                dates: true,
                vacancyLimits: true,
                categories: true,
                ranges: true,
                proponentTypes: true,
                workplan: true,
                statusLabels: true,
                phaseSeals: true,
                appealPhases: true,
                monitoringPhases: true,
            }
        },

        async doImport (modal) {
            try {
                const data = {
                    filters: this.filters,
                    opportunity: this.opportunity,
                }
                const api = new API('opportunity')
                const imported = await api.POST('import', data)
                modal.close()
                this.$emit('imported', imported)
            } catch (err) {
                console.error(err)
            }
        },

        isEmpty (value) {
            if (Array.isArray(value)) {
                return value.length === 0
            }
            return !value
        },

        parseFile (file) {
            const reader = new FileReader()
            reader.onload = (event) => {
                const json = event.target.result
                this.opportunity = JSON.parse(json)
            }
            reader.readAsText(file)
        },
    },
});
