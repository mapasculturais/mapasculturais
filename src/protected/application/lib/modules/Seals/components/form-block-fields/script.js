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
            spaces: $DESCRIPTIONS.space,
            agents: $DESCRIPTIONS.agent,
            lockedFields: $MAPAS.requestedEntity.lockedFields
        }
    },
    computed: {
        iterationFieldsSpaces() {
            const spaces = []
            let skip = [
                'createTimestamp',
                'id',
                'location',
                'name',
                'shortDescription',
                'status',
                'type',
                '_type',
                'userId',
            ];
            Object.keys($DESCRIPTIONS.space).forEach((item) => {
                if(!skip.includes(item) && $DESCRIPTIONS.space[item].required){
                    spaces.push({ label: $DESCRIPTIONS.space[item], value: false });
                }
            })
            return spaces
        },
        iterationFieldsAgents() {
            const agents = []
            let skip = [
                'createTimestamp',
                'id',
                'location',
                'name',
                'shortDescription',
                'status',
                'type',
                '_type',
                'userId',
            ];
            Object.keys($DESCRIPTIONS.agent).forEach((item) => {
                if(!skip.includes(item) && $DESCRIPTIONS.agent[item].required){
                    agents.push({ label: $DESCRIPTIONS.agent[item].label, value: false });
                }
            })
            return agents
        }
    }
    // data () {
    //     return {
    //         agents: [],
    //         spaces: []
    //     }
    // },
    // mounted () {
    //     this.agents = [
    //         { label: 'Lorem ipsum dolor sit amet', value: false },
    //         { label: 'Lorem ipsum dolor sit amet', value: false },
    //         { label: 'Lorem ipsum', value: false },
    //         { label: 'dolor sit', value: false },
    //         { label: 'sit amet', value: false }
    //     ]
    //     this.spaces = [
    //         { label: 'Lorem ipsum dolor sit amet', value: false },
    //         { label: 'Lorem ipsum dolor sit amet', value: false },
    //         { label: 'Lorem ipsum', value: false },
    //         { label: 'dolor sit', value: false },
    //         { label: 'sit amet', value: false }
    //     ]
    // }
});