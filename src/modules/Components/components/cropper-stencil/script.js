app.component('cropper-stencil', {

    template: $TEMPLATES['cropper-stencil'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    components: {
		StencilPreview: VueAdvancedCropper.StencilPreview,
		DraggableArea: VueAdvancedCropper.DraggableArea,
		DraggableElement: VueAdvancedCropper.DraggableElement,
		ResizeEvent: VueAdvancedCropper.ResizeEvent
	},

    props: {
        image: {
			type: Object
		},
		coordinates: {
			type: Object,
		},
		transitions: {
			type: Object,
		},
		stencilCoordinates: {
			type: Object,
		},
    },

    computed: {
		style() {
			const { height, width, left, top } = this.stencilCoordinates;
			const style = {
				width: `${width}px`,
				height: `${height}px`,
				transform: `translate(${left}px, ${top}px)`
			};
			if (this.transitions && this.transitions.enabled) {
				style.transition = `${this.transitions.time}ms ${this.transitions.timingFunction}`;
			}
			return style;
		}
	},

    methods: {
        onMove(moveEvent) {
			this.$emit('move', moveEvent);
		},
		onMoveEnd() {
        	this.$emit('move-end');
        },
		onResize(dragEvent) {
			const shift = dragEvent.shift();

			const widthResize = shift.left;
			const heightResize = -shift.top;

			this.$emit('resize', new ResizeEvent(
				{
					left: widthResize,
					right: widthResize,
					top: heightResize,
					bottom: heightResize,
				},
				{
					compensate: true,
				},
			));
		},
		onResizeEnd() {
        	this.$emit('resize-end');
        },
		aspectRatios() {
			return {
				minimum: 1,
				maximum: 1
			};
		}
    },
});
