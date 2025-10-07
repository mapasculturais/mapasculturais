app.component('opportunity-exporter', {
    template: $TEMPLATES['opportunity-exporter'],
    
    emits: ['exported'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },
    
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-exporter')
        return {text }
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
                const res = await this.entity.POST('export', this.filters)
                modal.close()
                this.$emit('exported', this.entity)
            } catch (err) {
                console.error(err)
            }
        },
    },
});
