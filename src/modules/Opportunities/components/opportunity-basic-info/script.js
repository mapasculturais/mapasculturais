app.component("opportunity-basic-info", {
	template: $TEMPLATES["opportunity-basic-info"],

	setup() {},

	data() {
		return {
			phases: [],
		};
	},

	async created() {
		if ($MAPAS.opportunityPhases && $MAPAS.opportunityPhases.length > 0) {
			this.phases = $MAPAS.opportunityPhases;
		} else {
			const api = new OpportunitiesAPI();
			this.phases = await api.getPhases(this.entity.id);
		}
		console.log(this.entity.registrationTo?._date);
		console.log(this.today);
		console.log(this.registrationToMinDate);
	},

	props: {
		entity: {
			type: Entity,
			required: true,
		},
	},

	computed: {
		lastPhase() {
			const phase = this.phases.find((item) => item.isLastPhase);
			return phase;
		},
		today() {
			return new Date();
		},
		registrationToMinDate() {
			return this.entity.registrationFrom?._date &&
				this.entity.registrationFrom?._date > this.today
				? this.entity.registrationFrom?._date
				: this.today;
		},
	},
});
