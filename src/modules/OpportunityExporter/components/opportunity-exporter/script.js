app.component('opportunity-exporter', {
    template: $TEMPLATES['opportunity-exporter'],
    
    emits: ['exported'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    data() {
        const filters = this.createFilters()

        return {
            filters,
        }
    },

    computed: {
        opportunityPhases(){
            return $MAPAS.opportunityPhases
        }
    },
    
    methods: {
        cancelExport (modal) {
            this.filters = this.createFilters()
            modal.close()
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

        async doExport (modal) {
            try {
                const exported = await this.entity.invoke('export', this.filters)
                modal.close()
                this.downloadJSON(exported)
                this.$emit('exported', exported)
            } catch (err) {
                console.error(err)
            }
        },

        downloadJSON (data) {
            const blob = new Blob([JSON.stringify(data)], { type: 'application/json' })
            const fileURL = URL.createObjectURL(blob)

            const downloadLink = document.createElement('a')
            downloadLink.href = fileURL
            downloadLink.download = `${this.entity.__objectId}.json`
            document.body.appendChild(downloadLink)
            downloadLink.click()
        },
    },
});
