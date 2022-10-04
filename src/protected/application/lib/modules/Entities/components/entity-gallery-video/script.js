
app.component('entity-gallery-video', {
    template: $TEMPLATES['entity-gallery-video'],
    
    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-gallery-video')
        return { text }
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
            videoList: {},
            galleryOpen: false,
            actualVideoIndex: null,
            actualVideo: {},
            metalist: {},
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: __('title', 'entity-gallery-video')
        },
        editable: {
            type: Boolean,
            default: false
        }
    },

    computed: {
        videos() {
            Object(this.entity.metalists.videos).forEach((content, index)=>{        
                content.video = this.getVideoBasicData(content.value);  
            });
            return this.entity.metalists.videos;
        }
    },
    
    methods: {
        // separa os dados do vídeo pela URL
        getVideoBasicData(url) {
            var parsedURL = new URL(url);
            var host = parsedURL.host;
            var provider = '';
            var videoID = '';
            var videoThumbnail = '';

            if (host.indexOf('youtube') != -1) {
                provider = 'youtube';
                videoID =  parsedURL.search.replace('?v=', '');
                videoThumbnail = 'https://img.youtube.com/vi/'+videoID+'/maxresdefault.jpg';
            } else if (host.indexOf('vimeo') != -1) {
                provider = 'vimeo';
                videoID = parsedURL.pathname.split('/')[1];
                videoThumbnail = 'https://vumbnail.com/'+videoID+'_large.jpg';
            }
            return {
                'parsedURL': parsedURL,
                'provider': provider,
                'videoID': videoID,
                'thumbnail': videoThumbnail
            }
        },
        // Abertura da modal
        open() {
            this.galleryOpen = true;
            if (!document.querySelector('body').classList.contains('galleryOpen'))
                document.querySelector('body').classList.add('galleryOpen');
        },
        // Fechamento da modal
        close() {
            this.galleryOpen = false;
            this.actualVideo = null;
            this.actualVideoIndex = null;
            
            if (document.querySelector('body').classList.contains('galleryOpen'))
                document.querySelector('body').classList.remove('galleryOpen');
        },
        // Abertura da imagem na modal
        openVideo(index) {
            this.actualVideo = this.entity.metalists.videos[index];
            this.actualVideoIndex = index;
        },
        // Avança entre os vídeos
        prev() {
            this.actualVideoIndex = (this.actualVideoIndex > 0) ? --this.actualVideoIndex : this.entity.metalists.videos.length-1 ;
            this.openVideo(this.actualVideoIndex);
        },
        // Recua entre os vídeos
        next() {
            this.actualVideoIndex = (this.actualVideoIndex < this.entity.metalists.videos.length-1) ? ++this.actualVideoIndex : 0 ;
            this.openVideo(this.actualVideoIndex);
        },

        create() {
            return this.entity.createMetalist('videos', this.metalist);      
        },

        save(metalist) {
            metalist.title = metalist.newData.title;
            metalist.value = metalist.newData.value;
            
            return metalist.save();
        }
    },
});
