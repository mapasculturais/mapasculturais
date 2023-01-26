app.component('timeline', {
    template: $TEMPLATES['timeline'],

    props: {
        timelineItems: {
          type: Array,
          default: () => []
        },
        messageWhenNoItems: {
          type: String,
          default: ''
        },
        colorDots: {
          type: String,
          default: '#1E1E1E'
        },
        uniqueTimeline: {
          type: Boolean,
          default: false
        },
        uniqueYear: {
          type: Boolean,
          default: false
        },
        order: {
          type: String,
          default: ''
        },
        dateLocale: {
          type: String,
          default: ''
        }
      },
      computed: {
        hasItems() {
          return !!this.timelineItems.length
        },
        dataTimeline() {
          if (this.order === 'desc')
            return this.orderItems(this.timelineItems, 'desc')
          if (this.order === 'asc')
            return this.orderItems(this.timelineItems, 'asc')
          return this.timelineItems
        }
      },
      methods: {
        wrapperItemClass(timelineIndex) {
          const isSameYearPreviousAndCurrent = this.checkYearTimelineItem(
            timelineIndex
          )
          const isUniqueYear =
            this.uniqueYear &&
            isSameYearPreviousAndCurrent &&
            this.order !== undefined
          return {
            'wrapper-item': true,
            'unique-timeline': this.uniqueTimeline || isUniqueYear
          }
        },
        checkYearTimelineItem(timelineIndex) {
          const previousItem = this.dataTimeline[timelineIndex - 1]
          const nextItem = this.dataTimeline[timelineIndex + 1]
          const currentItem = this.dataTimeline[timelineIndex]
          if (!previousItem || !nextItem) {
            return false
          }
          const fullPreviousYear = this.getYear(previousItem)
          const fullNextYear = this.getYear(nextItem)
          const fullCurrentYear = this.getYear(currentItem)
          return (
            (fullPreviousYear === fullCurrentYear &&
              fullCurrentYear === fullNextYear) ||
            fullCurrentYear === fullNextYear
          )
        },
        getYear(date) {
          return date.from.getFullYear()
        },
        hasYear(dataTimeline) {
          return (
            dataTimeline.hasOwnProperty('from') && dataTimeline.from !== undefined
          )
        },
        getTimelineItemsAssembled(items) {
          const itemsGroupByYear = []
          items.forEach(item => {
            const fullTime = item.from.getTime()
            if (itemsGroupByYear[fullTime]) {
              return itemsGroupByYear[fullTime].push(item)
            }
            itemsGroupByYear[fullTime] = [item]
          })
          return itemsGroupByYear
        },
        orderItems(items, typeOrder) {
          const itemsGrouped = this.getTimelineItemsAssembled(items)
          const keysItemsGrouped = Object.keys(itemsGrouped)
          const timeItemsOrdered = keysItemsGrouped.sort((a, b) => {
            if (typeOrder === 'desc') {
              return b - a
            }
            return a - b
          })
          const mappedItems = timeItemsOrdered.map(
            timeItem => itemsGrouped[timeItem]
          )
          return [].concat.apply([], mappedItems)
        }
    }

});
