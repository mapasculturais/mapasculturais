app.component('timeline-item', {
    template: $TEMPLATES['timeline-item'],

    props: {
        itemTimeline: {
          type: Object,
          default: () => ({})
        },
        colorDots: {
          type: String,
          default: '#2da1bf'
        },
        dateLocale: {
          type: String,
          default: ''
        }
      },
      methods: {
        getBackgroundColour(color) {
          return color ? `background:${color}` : `background:${this.colorDots}`
        },
        getFormattedDate(item) {
          const locale = this.dateLocale || window.navigator.language
          const nameMonth = item.from.toLocaleDateString(locale, { month: 'long' })
          if (item.showDayAndMonth) {
            const day = item.from.getDate()
            return `${day}. ${nameMonth}`
          }
          return nameMonth
        }
      }

});
