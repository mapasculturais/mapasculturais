app.component('opportunity-basic-info' , {
    template: $TEMPLATES['opportunity-basic-info'],

    data () {
        return {
            locale: $MAPAS.config.locale,
            dateStart: '',
            dateEnd: '',
            dateFinalResult: '',
            dayNames: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
            accountability: false
        };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    methods: {
        dateFormat(date) {
            console.log(date);
            return '';
        }
    }
});