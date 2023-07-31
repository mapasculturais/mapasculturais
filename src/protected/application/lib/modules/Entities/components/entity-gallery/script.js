app.component('entity-gallery', {
    template: $TEMPLATES['entity-gallery'],
    emits: [],

    created() {
        window.addEventListener('keydown', (e) => {
            if (this.galleryOpen) {
                switch(e.key) {
                    case 'Escape':      this.close();   break;
                    case 'ArrowLeft':   this.prev();    break;
                    case 'ArrowRight':  this.next();    break;
                }            
            }
        });
    },

    data() {
        return {
            galleryOpen: false,
            actualImgIndex: null,
            actualImg: null
        }
    },

    computed: {
        images() {
            return this.entity.files.gallery;
        }
    }, 

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: 'Galeria de fotos'
        },
        editable: {
            type: Boolean,
            default: false
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },

    },
    
    methods: {
        // Abertura da modal
        open() {
            this.galleryOpen = true;
            if (!document.querySelector('body').classList.contains('galleryOpen'))
                document.querySelector('body').classList.add('galleryOpen');
        },
        // Fechamento da modal
        close() {
            this.galleryOpen = false;
            this.actualImg = null;
            this.actualImgIndex = null;

            if (document.querySelector('body').classList.contains('galleryOpen'))
                document.querySelector('body').classList.remove('galleryOpen');
        },
        // Abertura da imagem na modal
        openImg(index) {
            this.actualImg = this.images[index];
            this.actualImgIndex = index;
        },
        // Avança entre as imagens
        prev() {
            this.actualImgIndex = (this.actualImgIndex > 0) ? --this.actualImgIndex : this.images.length-1 ;
            this.openImg(this.actualImgIndex);
        },
        // Recua entre as imagens
        next() {
            this.actualImgIndex = (this.actualImgIndex < this.images.length-1) ? ++this.actualImgIndex : 0 ;
            this.openImg(this.actualImgIndex);
        },

        rename(img, popopver) {
            img.description = img.newDescription;
            img.save().then(() => popopver.close());
        } 
    },
});
