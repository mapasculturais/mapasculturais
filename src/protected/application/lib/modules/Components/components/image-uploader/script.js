function getMimeType(file, fallback = null) {
	const byteArray = (new Uint8Array(file)).subarray(0, 4);
    let header = '';
    for (let i = 0; i < byteArray.length; i++) {
       header += byteArray[i].toString(16);
    }
	switch (header) {
        case "89504e47":
            return "image/png";
        case "47494638":
            return "image/gif";
        case "ffd8ffe0":
        case "ffd8ffe1":
        case "ffd8ffe2":
        case "ffd8ffe3":
        case "ffd8ffe8":
            return "image/jpeg";
        default:
            return fallback;
    }
}

app.component('image-uploader', {
    template: $TEMPLATES['image-uploader'],
    emits: [],

	components: {
		Cropper: VueAdvancedCropper.Cropper,
	},

    props: {
        group: {
            type: String,
            required: true
        },
        circular: {
            type: Boolean,
            default: false
        },
        aspectRatio: {
            type: Number,
            required: false
        },
        width: {
            type: Number,
            required: false
        },
        height: {
            type: Number,
            required: false
        }
    },

    computed: {
        stencilProps () {
            if (!this.aspectRatio && this.width && this.height) {
                return { aspectRatio: this.width / this.height };
            } else if(this.aspectRatio) {
                return { aspectRatio: this.aspectRatio};
            } else {
                return {
                    maxAspectRatio: 16/8,
                    minAspectRatio: 3/4 
                }
            }
        },

        defaultSize () {
            let width = this.width;
            let height = this.height;
            let aspectRatio = this.aspectRatio;

            if (aspectRatio && width && !height) {
                height = width / aspectRatio;
            } else if (aspectRatio && height && !width) {
                width = height * aspectRatio; 
            }

            if (width && height) {
                return {
                    width: width,
                    height: height,
                };
            } 
        }
    },

	data() {
		return {
			image: {
                name: null,
				src: null,
				type: null
			},
		};
	},
	methods: {

		crop(modal) {
            const mimeType = this.image.type;
            const filename = this.image.type;
			const { canvas } = this.$refs.cropper.getResult()
			canvas.toBlob((blob) => {
                const extension = mimeType.split('/')[1];
				const file = new File([blob], filename, { type: mimeType });
			}, this.image.type);
		},
		reset() {
			this.image = {
                name: null,
				src: null,
				type: null
			}
		},
		loadImage(event, modal) {
            modal.open();
			// Reference to the DOM input element
			const { files } = event.target;
            const filename = event.target.value.split(/(\\|\/)/g).pop();

			// Ensure that you have a file before attempting to read it
			if (files && files[0]) {
                

				// 1. Revoke the object URL, to allow the garbage collector to destroy the uploaded before file
				if (this.image.src) {
					URL.revokeObjectURL(this.image.src)
				}
				// 2. Create the blob link to the file to optimize performance:
				const blob = URL.createObjectURL(files[0]);
				
				// 3. The steps below are designated to determine a file mime type to use it during the 
				// getting of a cropped image from the canvas. You can replace it them by the following string, 
				// but the type will be derived from the extension and it can lead to an incorrect result:
				//
				// this.image = {
				//    src: blob;
				//    type: files[0].type
				// }
				
				// Create a new FileReader to read this image binary data
				const reader = new FileReader();
				// Define a callback function to run, when FileReader finishes its job
				reader.onload = (e) => {
					// Note: arrow function used here, so that "this.image" refers to the image of Vue component
					this.image = {
                        name: filename,
						// Set the image source (it will look like blob:http://example.com/2c5270a5-18b5-406e-a4fb-07427f5e7b94)
						src: blob,
						// Determine the image type to preserve it during the extracting the image from canvas:
						type: getMimeType(e.target.result, files[0].type),
					};
				};
				// Start the reader job - read file as a data url (base64 format)
				reader.readAsArrayBuffer(files[0]);
			}
		},
	},
});
