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
            importStatus: 0,
            importing: false,
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
            this.importStatus = 0
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

            const infos = !this.shouldOverrideInfos
                ? this.opportunity.infos
                : {
                    name: this.infos.name,
                    terms: this.infos.terms,
                    type: this.infos.type,
                }
            const data = {
                filters: this.filters,
                status: this.importStatus,
                opportunity: {
                    ...this.opportunity,
                    infos,
                    ownerEntity: {
                        __objectType: this.infos.ownerEntity.__objectType,
                        _id: this.infos.ownerEntity._id,
                    },
                },
            }

            this.importing = true
            modal.loading(true)

            try {
                const api = new API('opportunity')
                const res = await api.POST('import', data)
                let payload = {}
                try {
                    payload = await res.json()
                } catch (e) {
                    payload = {}
                }

                if (!res.ok) {
                    const errEntity = new Entity('opportunity')
                    errEntity.catchErrors(res, payload)
                    return
                }

                if (payload == null || payload.id == null) {
                    useMessages().error(this.text('erroAoImportar'))
                    return
                }

                const entity = api.getEntityInstance(payload.id)
                entity.populate(payload)
                Utils.pushEntityToList(entity)

                const messages = useMessages()
                messages.success(this.text('importacaoRealizadaComSucesso'))

                const tabSlug = this.importStatus === 1 ? 'publish' : 'draft'
                if (window.location.hash.slice(1) !== tabSlug) {
                    window.location.hash = '#' + tabSlug
                }

                modal.close()
                this.infos = this.createInfos()
                this.opportunity = null
                this.importStatus = 0
                this.$emit('imported', entity)
            } catch (err) {
                console.error(err)
                const messages = useMessages()
                messages.error(this.text('erroAoImportar'))
            } finally {
                this.importing = false
                modal.loading(false)
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
