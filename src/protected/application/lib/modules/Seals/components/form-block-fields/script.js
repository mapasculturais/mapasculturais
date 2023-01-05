app.component('form-block-fields', {
    template: $TEMPLATES['form-block-fields'],
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
    data () {
        return {
            spaces: [],
            agents: []
        }
    },
    mounted () {
        this.iterationAgentFields()
        this.iterationSpaceFields()
    },
    methods: {
        iterationAgentFields() {
            let skip = [
                'createTimestamp',
                'id',
                'location',
                'type',
                '_type',
                'userId',
            ];
            Object.keys($DESCRIPTIONS.agent).forEach((item)=>{
                if(!skip.includes(item) && $DESCRIPTIONS.agent[item].required){
                    this.agents.push({ value: item, label: $DESCRIPTIONS.agent[item].label });
                }
            })
        },
        iterationSpaceFields() {
            let skip = [
                'createTimestamp',
                'id',
                'location',
                'type',
                '_type',
                'userId',
            ];
            Object.keys($DESCRIPTIONS.space).forEach((item)=>{
                if(!skip.includes(item) && $DESCRIPTIONS.space[item].required){
                    this.spaces.push({ value: item, label: $DESCRIPTIONS.space[item].label });
                }
            })
        },
        saveValueSpaceLockedFields (val) {
            console.log(val)
            $MAPAS.requestedEntity.lockedFields.push({ space: val })
            console.log($MAPAS.requestedEntity.lockedFields)
        },
        saveValueAgentLockedFields (val) {
            console.log(val)
            $MAPAS.requestedEntity.lockedFields.push({ agent: val })
            console.log($MAPAS.requestedEntity.lockedFields)
        }
    }
});