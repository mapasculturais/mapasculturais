
app.component('entity-gallery-video', {
    template: $TEMPLATES['entity-gallery-video'],
    
    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-gallery-video')
        return { text }
    },

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
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },

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
            try {
                var parsedURL = new URL(url);
                var host = parsedURL.host;
                var provider = '';
                var videoID = '';
                var videoThumbnail = '';

                var ytRegex = /(youtu.*be.*)\/(watch\?v=|embed\/|v|shorts|)(.*?((?=[&#?])|$))/;
                var vmRegex = /(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:[a-zA-Z0-9_\-]+)?/i;

                if (host.indexOf('youtube') != -1 || host.indexOf('youtu.be') != -1) {
                    provider = 'youtube';
                    videoID = parsedURL.href.match(ytRegex)[3];
                    videoThumbnail = 'https://img.youtube.com/vi/'+videoID+'/0.jpg';
                } else if (host.indexOf('vimeo') != -1) {
                    provider = 'vimeo';
                    videoID = parsedURL.href.match(vmRegex)[1];
                    videoThumbnail = 'https://vumbnail.com/'+videoID+'.jpg';
                }

                return {
                    'parsedURL': parsedURL,
                    'provider': provider,
                    'videoID': videoID,
                    'thumbnail': videoThumbnail
                }
            } catch (e) {
                console.error(`erro na galeria - ${e}`);
                return {};
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
        // Adiciona video na entidade
        async create(popover) {
            if(!this.metalist.value || !this.metalist.title){
                const messages = useMessages();
                messages.error(this.text('preencha todos os campos'));
                return;
            }
            await this.entity.createMetalist('videos', this.metalist);
            popover.close();
        },
        // Salva modificações nos vídeos adicionados
        async save(metalist, popover) {
            if(!metalist.newData.title || !metalist.newData.value) {
                const messages = useMessages();
                messages.error(this.text('preencha todos os campos'));
                return;
            }
            metalist.title = metalist.newData.title;
            metalist.value = metalist.newData.value;
            
            await metalist.save();
            popover.close();
        }
    },
});
