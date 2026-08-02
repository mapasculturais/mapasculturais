
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

    mounted() {
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
            if (this.videoList[url]) {
                return this.videoList[url];
            }

            try {
                const parsedURL = new URL(url);
                const host = parsedURL.hostname.toLowerCase().replace(/\.$/, '');
                let provider = '';
                let videoID = '';
                let videoThumbnail = '';

                const ytRegex = /(youtu.*be.*)\/(watch\?v=|embed\/|v|shorts|)(.*?((?=[&#?])|$))/;
                const vmRegex = /(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:[a-zA-Z0-9_\-]+)?/i;

                if (parsedURL.host.indexOf('youtube') != -1 || parsedURL.host.indexOf('youtu.be') != -1) {
                    provider = 'youtube';
                    videoID = parsedURL.href.match(ytRegex)[3];
                    videoThumbnail = 'https://img.youtube.com/vi/'+videoID+'/0.jpg';
                } else if (parsedURL.host.indexOf('vimeo') != -1) {
                    provider = 'vimeo';
                    videoID = parsedURL.href.match(vmRegex)[1];
                    videoThumbnail = 'https://vumbnail.com/'+videoID+'.jpg';
                } else if (this.isSupportedVideoHost(host, 'tiktok')) {
                    provider = 'tiktok';
                } else if (
                    this.isSupportedVideoHost(host, 'instagram')
                ) {
                    provider = 'instagram';
                }

                this.videoList[url] = {
                    parsedURL,
                    provider,
                    videoID,
                    thumbnail: videoThumbnail,
                };

                if (provider === 'tiktok' || provider === 'instagram') {
                    this.loadRemoteThumbnail(url);
                }

                return this.videoList[url];
            } catch (error) {
                console.error(`erro na galeria - ${error}`);
                return {};
            }
        },
        isSupportedVideoHost(host, provider) {
            const providerHosts = {
                tiktok: [
                    'tiktok.com',
                    'www.tiktok.com',
                    'm.tiktok.com',
                    'vm.tiktok.com',
                    'vt.tiktok.com',
                ],
                instagram: [
                    'instagram.com',
                    'www.instagram.com',
                    'm.instagram.com',
                    'instagr.am',
                ],
            };

            return providerHosts[provider].includes(host);
        },
        async loadRemoteThumbnail(url) {
            try {
                const endpoint = Utils.createUrl('site', 'videoThumbnail');
                endpoint.searchParams.set('url', url);

                const response = await fetch(endpoint, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                if (typeof data.thumbnailUrl === 'string' && this.videoList[url]) {
                    this.videoList[url].thumbnail = data.thumbnailUrl;
                }
            } catch (error) {
                // A ausência de thumbnail mantém o comportamento visual atual.
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
            if(!metalist.newData.title) {
                const messages = useMessages();
                messages.error(this.text('preencha todos os campos'));
                return;
            }
            metalist.title = metalist.newData.title;
            // Mantém o value original, não permite editar
            
            await metalist.save();
            popover.close();
        }
    },
});
