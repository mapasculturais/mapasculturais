function getMimeType(file, fallback = null) {
    const byteArray = new Uint8Array(file).subarray(0, 4);
    let header = "";
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

app.component("mc-image-uploader", {
    template: $TEMPLATES["mc-image-uploader"],
    emits: ["uploaded", "cropped"],

    components: {
        Cropper: VueAdvancedCropper.Cropper,
        CircleStencil: VueAdvancedCropper.CircleStencil,
    },

    props: {
        entity: {
            type: Entity,
            required: true,
        },
        group: {
            type: String,
            required: true,
        },
        circular: {
            type: Boolean,
            default: false,
        },
        aspectRatio: {
            type: Number,
            required: false,
        },
        width: {
            type: Number,
            required: false,
        },
        height: {
            type: Number,
            required: false,
        },
        useDescription: {
            type: Boolean,
            default: false,
        },
        deleteFile: {
            type: Boolean,
            default: false
        },
    },
    computed: {
        stencilProps() {
            if (!this.aspectRatio && this.width && this.height) {
                return { aspectRatio: this.width / this.height };
            } else if (this.aspectRatio) {
                return { aspectRatio: this.aspectRatio };
            } else {
                return {
                    maxAspectRatio: 16 / 9,
                    minAspectRatio: 9 / 16,
                };
            }
        },

        blobUrl() {
            return this.blob ? URL.createObjectURL(this.blob) : "";
        },
        showDelete() {
            return this.entity.files[this.group] && this.deleteFile;
        }
    },

    data() {
        return {
            image: {
                name: null,
                src: null,
                type: null,
            },
            description: "",
            blob: null,
            file: null,
            modal: null,
        };
    },

    methods: {
        crop(modal) {
            const mimeType = this.image.type;
            const filename = this.image.name;
            const { canvas } = this.$refs.cropper.getResult();

            this.modal = modal;

            canvas.toBlob((blob) => {
                this.blob = blob;
                this.file = new File([this.blob], filename, { type: mimeType });
                this.$emit("cropped", this);
                this.upload();
            }, this.image.type);
        },

        upload() {
            if (!this.file) {
                return false;
            }

            let data = {
                group: this.group,
                description: this.description,
            };

            this.entity.upload(this.file, data).then((response) => {
                this.$emit("uploaded", this);
                this.modal.close();
            });

            return true;
        },

        reset() {
            this.file = null;
            this.blob = null;
            this.description = "";
            this.image = {
                name: null,
                src: null,
                type: null,
            };
        },

        loadImage(event, modal) {
            const { files } = event.target;

            if (files && files[0]) {
                modal.open();
                const filename = event.target.value.split(/(\\|\/)/g).pop();

                // Ensure that you have a file before attempting to read it
                // 1. Revoke the object URL, to allow the garbage collector to destroy the uploaded before file
                if (this.image.src) {
                    URL.revokeObjectURL(this.image.src);
                }
                // 2. Create the blob link to the file to optimize performance:
                const blob = URL.createObjectURL(files[0]);

                let img = new Image();
                img.src = blob;
                let size;
                img.onload = async () => {
                    size = {
                        width: img.width,
                        height: img.height,
                    };
                };
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
                        ...size,
                    };
                };
                // Start the reader job - read file as a data url (base64 format)
                reader.readAsArrayBuffer(files[0]);
            }
        },

        delFile() {
            this.entity.files[this.group].delete().then(() => {
                this.file = null;
            });
        },

        defaultSize() {
            let width, height;

            if (this.aspectRatio && this.image) {
                if (this.image.width > this.image.height) {
                    height = this.image.height;
                } else {
                    width = this.image.width;
                }
            } else if (this.image) {
                if (this.image.width > this.image.height) {
                    height = this.image.height;
                    width = Math.max(this.image.width, this.image.height * 16 / 9)
                } else {
                    width = this.image.width;
                    height = Math.max(this.image.height, this.image.width * 16 / 9)
                }
            }

            if (this.circular && this.image.width && this.image.height) {
                return {
                    width: this.image.width,
                    height: this.image.height,
                };
            }

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
        },
    },
});
