app.component('entity-gallery', {
    template: $TEMPLATES['entity-gallery'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {
        window.addEventListener('keydown', (e) => {
            switch(e.key) {
                case 'Escape':      this.close();   break;
                case 'ArrowLeft':   this.prev();    break;
                case 'ArrowRight':  this.next();    break;
            }            
        });
    },

    data() {
        return {
            images: this.entity.files.gallery,
            galleryOpen: false,
            actualImgIndex: null,
            actualImg: null
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
        }
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
        }
    },
});
