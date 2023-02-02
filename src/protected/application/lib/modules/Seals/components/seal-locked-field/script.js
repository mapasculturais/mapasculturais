app.component('seal-locked-field', {
    template: $TEMPLATES['seal-locked-field'],
    props: {
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },
    watch: {
      agents: {
          handler(agents) {
              this.buildLockedFields()
          },
          deep: true
      },
      spaces: {
          handler(spaces) {
              this.buildLockedFields()
          },
          deep: true
      }
    },
    data () {
        return {
            spaces: [],
            agents: [],
            taxonomiesAgents: [],
            taxonomiesSpaces: []
        }
    },
    mounted () {
        this.iterationAgentFields()
        this.iterationSpaceFields()

        const lockedFields = [...$MAPAS.requestedEntity.lockedFields]
        if(lockedFields.length > 0) {
            lockedFields.forEach(item => {
                const lockedFieldSplit = item.split('.')
                const entity = lockedFieldSplit[0]
                const entityValue = lockedFieldSplit[1]
                if(entity == 'agent') {
                    const agents = [...this.agents]
                    const agent = agents.find(agent => agent.item === entityValue)
                    if(agent) {
                        agent.value = true
                    }
                }
                if(entity == 'space') {
                    const spaces = [...this.spaces]
                    const space = spaces.find(space => space.item == entityValue)
                    if(space) {
                        space.value = true
                    }
                }
            })

        }

    },
    methods: {
        iterationAgentFields() {
            const skip = $MAPAS.config.sealLockedSkipedFields.agents;
            Object.keys($DESCRIPTIONS.agent).forEach((item)=>{
                if(!skip.includes(item) && !$DESCRIPTIONS.agent[item].isEntityRelation ){
                    this.agents.push({ value: false, label: $DESCRIPTIONS.agent[item].label, item });
                }
            })
            Object.keys($TAXONOMIES).forEach((item)=>{
                if(!skip.includes(item)){
                    this.taxonomiesAgents.push({ value: false, label: $TAXONOMIES[item].description, item });
                }
            })
        },
        iterationSpaceFields() {
            const skip = $MAPAS.config.sealLockedSkipedFields.spaces;

            Object.keys($DESCRIPTIONS.space).forEach((item)=>{
                if(!skip.includes(item) && !$DESCRIPTIONS.space[item].isEntityRelation){
                    this.spaces.push({ value: false, label: $DESCRIPTIONS.space[item].label, item });
                }
            })
            Object.keys($TAXONOMIES).forEach((item)=>{
                if(!skip.includes(item)){
                    this.taxonomiesSpaces.push({ value: false, label: $TAXONOMIES[item].description, item });
                }
            })
        },
        buildLockedFields () {
            const agentsSelected = this.agents.filter(agent => agent.value)?.map(agent => 'agent.' + agent.item) || []
            const spacesSelected = this.spaces.filter(space => space.value)?.map(space => 'space.' + space.item) || []
            this.entity.lockedFields = [...agentsSelected, ...spacesSelected]
        }
    }
});