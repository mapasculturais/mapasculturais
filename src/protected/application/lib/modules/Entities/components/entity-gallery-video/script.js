/**
 * Vue Lifecycle
 * 1. setup
 * 2. beforeCreate
 * 3. created
 * 4. beforeMount
 * 5. mounted
 * 
 * // sempre que há modificação nos dados
 *  - beforeUpdate
 *  - updated
 * 
 * 6. beforeUnmount
 * 7. unmounted                  
 */

app.component('entity-gallery-video', {
    template: $TEMPLATES['entity-gallery-video'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-gallery-video')
        return { text }
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

    data() {
        return {    
            videoList: {},
            galleryOpen: false,
            actualVideoIndex: null,
            actualVideo: {}
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
        // adiciona dados do video ao array
        videos() {
            Object(this.entity.metalists.videos).forEach((content, index)=>{        
                content.data = this.getVideoBasicData(content.value);  
            });
            return this.entity.metalists.videos;
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
        }
    },
});
