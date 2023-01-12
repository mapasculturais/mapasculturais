app.component('seal-relation-view', {
    template: $TEMPLATES['seal-relation-view'],
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
    computed: {
      seal () {
        return $MAPAS.requestedEntity?.seal
      },
      agent () {
        return $MAPAS.requestedEntity?.agent
      },
      certificateText () {
          return $MAPAS.requestedEntity?.certificateText
      },
      dateValidFormatted () {
          const dateObj = new Date($MAPAS.requestedEntity?.validateDate?.date)
          return dateObj.toLocaleDateString("pt-BR")
      },
      dateCreated () {
          const dateObj = new Date($MAPAS.requestedEntity?.createTimestamp?.date)
          return dateObj.toLocaleDateString("pt-BR")
      },
      isValid () {
          const validDateString = $MAPAS.requestedEntity?.validateDate?.date
          const now = new Date()
          const validDate = new Date(validDateString)
          return validDate.getTime() > now.getTime()
      }
    }
});