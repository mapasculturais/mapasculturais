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
            infos: this.createInfos(),
            opportunity: null,
        }
    },

    computed: {
        shouldOverrideInfos () {
            if (!this.opportunity) {
                return false
            }
            return !this.filters.infos
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
            this.infos = this.createInfos()
            this.opportunity = null
        },

        createFilters () {
            return {
                infos: true,
                files: true,
                images: true,
                dates: true,
                vacancyLimits: true,
                workplan: true,
                statusLabels: true,
                phaseSeals: true,
                appealPhases: true,
                monitoringPhases: true,
            }
        },

        createInfos () {
            const entity = new Entity('opportunity')
            entity.terms = { area: [] }
            return Vue.reactive(entity)
        },

        async doImport (modal) {
            if (!this.validate()) {
                return
            }

            try {
                const infos = !this.shouldOverrideInfos
                    ? this.opportunity.infos
                    : {
                        name: this.infos.name,
                        terms: this.infos.terms,
                        type: this.infos.type,
                    }
                const data = {
                    filters: this.filters,
                    opportunity: {
                        ...this.opportunity,
                        infos,
                        ownerEntity: {
                            __objectType: this.infos.ownerEntity.__objectType,
                            _id: this.infos.ownerEntity._id,
                        },
                    },
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

        validate () {
            let isValid = true
            this.infos.__validationErrors = {}

            if (!this.infos.ownerEntity) {
                this.infos.__validationErrors['objectType'] = [this.text('Campo obrigatório')]
                isValid = false
            }

            if (this.shouldOverrideInfos) {
                if (!this.infos.type) {
                    this.infos.__validationErrors['type'] = [this.text('Campo obrigatório')]
                    isValid = false
                }

                if (!this.infos.name) {
                    this.infos.__validationErrors['name'] = [this.text('Campo obrigatório')]
                    isValid = false
                }

                if (this.infos.terms.area.length === 0) {
                    this.infos.__validationErrors['term-area'] = [this.text('Campo obrigatório')]
                    isValid = false
                }
            }

            return isValid
        },
    },
});
