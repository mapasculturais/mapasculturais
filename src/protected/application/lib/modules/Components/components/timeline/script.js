app.component('timeline', {
	template: $TEMPLATES['timeline'],

	props: {
		big: {
			type: Boolean,
			default: false
		}

		/* timelineItems: {
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
		} */
	},

	data() {
        return {
            teste:
			[
				{
					"@entityType": "opportunity",
					"id": 222,
					"type": 1,
					"status": 1,
					"name": "Nome da Oportunidade",
					"registrationFrom": new McDate('03/05/2023'),
					"registrationTo": new McDate('03/21/2023'),
					"publishedRegistrations": false,
					"publishTimestamp": "DateTime",
					"isFirstPhase": true,
					"isLastPhase": false,
					"summary": { // fase (oportunidade) com avaliação só retorna 'registrations', 'sent' e 'draft'
						"registrations": 33,
						"sent": 13, // status > 0
						"draft": 20 // status = 0
					}
				},
				{
					"@entityType": "evaluationmethodconfiguration",
					"id": 200,
					"opportunity": 222,
					"type": "documentary",
					"status": 1,
					"name": "Avaliação Documental",
					"evaluationFrom": new McDate('04/05/2023'),
					"evaluationTo": new McDate('04/21/2023'),
					"publishedRegistrations": false, // vem da oportunidade 222
					"publishTimestamp": "DateTime", // vem da oportunidade 222
			
					"summary": {
						"registrations": 13, // considera somente as enviadas
						"evaluated": 10,
						"approved": 5,
						"waitlist": 2,
						"notapproved": 1,
						"invalid": 2
					}
				},
				{
					"@entityType": "evaluationmethodconfiguration",
					"id": 201,
					"opportunity": 224, // fase sem coleta de dados, não é retornada no endpoint
					"type": "technical",
					"status": 1,
					"name": "Avaliação Técnica",
					"evaluationFrom": new McDate('05/05/2023'),
					"evaluationTo": new McDate('05/21/2023'),
					"publishedRegistrations": false, // vem da oportunidade 224
					"publishTimestamp": "DateTime", // vem da oportunidade 224
			
					"summary": {
						"registrations": 5, // só são trazidas da fase anterior as aprovadas
						"evaluated": 4,
						"approved": 3,
						"waitlist": 1,
						"notapproved": 0,
						"invalid": 0
					}
				},
				{
					"@entityType": "opportunity",
					"id": 225,
					"type": 1,
					"status": 1,
					"name": "Fase de coleta de dados sem avaliação",
					"registrationFrom": new McDate('06/05/2023'),
					"registrationTo": new McDate('06/21/2023'),
					"publishedRegistrations": false,
					"publishTimestamp": "DateTime",
					"isFirstPhase": false,
					"isLastPhase": false,
					"summary": { // fase (oportunidade) sem avaliação só retorna todos os status
						"registrations": 3,
						"sent": 3, // status > 0
						"draft": 0, // status = 0
						"pending": 0, // status = 1
						"approved": 2,
						"waitlist": 1,
						"notapproved": 0,
						"invalid": 0
					}
				},
				{
					"@entityType": "opportunity",
					"id": 223,
					"type": 1,
					"status": -1, // pensar se vai ser isso mesmo
					"name": "Publicação do resultado",
					"registrationFrom": new McDate('07/05/2023'),
					"registrationTo": new McDate('07/21/2023'),
					"publishedRegistrations": false,
					"publishTimestamp": "DateTime",
					"isFirstPhase": false,
					"isLastPhase": true,
					"summary": { // já importa com os status da fase anterior, possibilita que no futuro implementemos uma revisão antes da publicação, sem mudar o resultado da fase de avaliação
						"registrations": 3, // 
						"pending": 0, // status = 1
						"approved": 2,
						"waitlist": 1,
						"notapproved": 0,
						"invalid": 0
					}
				},
			]
        }
    },

	computed: {
		/* hasItems() {
			return !!this.timelineItems.length
		},

		dataTimeline() {
			if (this.order === 'desc')
				return this.orderItems(this.timelineItems, 'desc')
			if (this.order === 'asc')
				return this.orderItems(this.timelineItems, 'asc')
			return this.timelineItems
		} */
	},

	methods: {

		dateFrom(id) {
			let item = this.getItemById(id);

			if (item.registrationFrom) {
				return item.registrationFrom.date('2-digit year');
			}	
			if (item.evaluationFrom) {
				return item.evaluationFrom.date('2-digit year');
			}
			return false;
		},

		dateTo(id) {
			let item = this.getItemById(id);

			if (item.registrationTo) {
				return item.registrationTo.date('2-digit year');
			}	
			if (item.evaluationTo) {
				return item.evaluationTo.date('2-digit year');
			}
			return false;
		},

		hour(id) {
			let item = this.getItemById(id);

			if (item.registrationTo) {
				return item.registrationTo.hour('2-digit')+'h';
			}
			if (item.evaluationTo) {
				return item.evaluationTo.hour('2-digit')+'h';
			}
			return false;
		},

		isActive(id) {
			let item = this.getItemById(id);

			if (item.registrationFrom && item.registrationTo) {
				if (item.registrationFrom._date <= new Date() && item.registrationTo._date >= new Date()) {
					return true;
				}
			}
			if (item.evaluationFrom && item.evaluationTo) {
				if (item.evaluationFrom._date <= new Date() && item.evaluationTo._date >= new Date()) {
					return true;
				}
			}
			return false;
		},

		getItemById(id) {
			return this.teste.find(x => x.id === id);
		}

		/* wrapperItemClass(timelineIndex) {
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
		} */
	}

});
