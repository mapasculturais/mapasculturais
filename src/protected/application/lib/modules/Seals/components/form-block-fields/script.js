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
            agents: [],
            spaces: []
        }
    },
    mounted () {
        this.agents = [
            { label: 'Lorem ipsum dolor sit amet', value: false },
            { label: 'Lorem ipsum dolor sit amet', value: false },
            { label: 'Lorem ipsum', value: false },
            { label: 'dolor sit', value: false },
            { label: 'sit amet', value: false }
        ]
        this.spaces = [
            { label: 'Lorem ipsum dolor sit amet', value: false },
            { label: 'Lorem ipsum dolor sit amet', value: false },
            { label: 'Lorem ipsum', value: false },
            { label: 'dolor sit', value: false },
            { label: 'sit amet', value: false }
        ]
    }
});