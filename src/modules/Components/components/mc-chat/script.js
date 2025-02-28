app.component('mc-chat', {
    template: $TEMPLATES['mc-chat'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-chat');
        return { text, hasSlot }
    },

    props: {
        anonymousSender: {
            type: String,
            default: null
        },
        thread: {
            type: Entity,
            required: true
        },
        pingPong: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            attachmentMessage: null,
            autoRefreshInterval: null,
            currentTextareaFocus: false,
            currentUser: useGlobalState().auth.user,
            entities: [],
            message: this.createNewMessage(''),
            newAttachmentMessage: null,
            processing: false,
            query: null,
            threadStatus: null,
            chatEntities: [],
        };
    },

    created() {
    },

    mounted() {
        if (this.thread && this.thread.id) {
            this.query = {
                thread: `EQ(${this.thread.id})`
            };

            // adiciona o status ao data quando a thread está definida
            this.threadStatus = this.thread.status;
            this.startAutoRefresh();
        } else {
            console.log('Thread não está definida no mounted.');
        }

        globalThis.addEventListener('afterFetch', (e) => {
            const header = e.detail.headers.get('MC-Thread-Status');
            if(header !== null){
                this.threadStatus = Number(header);
            }
        });
    },

    computed: {
        chatOwner() {
            return this.thread?.owner;
        },

        lastMessageIsMine() {
            const action = $MAPAS.request.action;
            const lastMessage = this.chatEntities[0];

            if (!lastMessage && action === 'evaluation') {
                return true;
            } 

            return lastMessage ? this.isMine(lastMessage) : false;
        },

        // verifica se o chat está fechado
        isClosed() {
            return this.threadStatus === $MAPAS.config.chatThreadStatusClosed;
        },
    },

    beforeUnmount() {
        this.clearAutoRefresh();
    },

    methods: {
        async sendMessage() {
            if ((typeof this.message.payload) === 'string' && this.message.payload.trim() === '') {
                return;
            } 

            if ((typeof this.message.payload) === 'object' && this.message.payload.message.trim() === '') {
                return;
            }

            this.processing = true;
            const messages = useMessages();

            try {
                const newMessage = this.message; 
                await newMessage.save();

                const attachment = this.$refs.attachment;

                if (attachment.file) {
                    await attachment.upload();
                }

                this.$refs.chatMessages.entities.unshift(newMessage);

                messages.success(this.text('Mensagem enviada com sucesso'));
                this.message = this.createNewMessage('');
            } catch (error) {
                messages.error(error?.data);
            } finally {
                this.processing = false;
            }
        },

        createNewMessage(payload) {
            const newMessage = new Entity('chatmessage');
            newMessage.disableMessages();
            newMessage.thread = this.thread;
            newMessage.payload = payload;
            return newMessage;
        },

        addNewMessages(newMessages) {
            const entities = this.$refs.chatMessages.entities;
            newMessages.forEach((message) => {
                if (!entities.some((entity) => entity.id === message.id)) {
                    entities.unshift(message);
                }
            });
        },

        async fetchNewMessages() {
            const entities = this.$refs.chatMessages.entities;
            const lastTimestamp = entities[0]?.createTimestamp?.sql('full') || null;

            try {
                const api = new API('chatmessage');
                const selectFields = this.anonymousSender
                    ? 'createTimestamp,payload,user,files'
                    : 'createTimestamp,payload,user.profile.{name,files.avatar},files';

                const params = {
                    thread: `EQ(${this.thread.id})`,
                    '@select': selectFields,
                    '@order': 'createTimestamp DESC',
                    '@limit': 100,
                    '@page': 1,
                };

                if (lastTimestamp) {
                    params.createTimestamp = `GT(${lastTimestamp})`;
                }

                const newMessages = await api.find(params);

                this.addNewMessages(newMessages.reverse());
            } catch (error) {
                console.error('Erro ao buscar novas mensagens:', error);
            }
        },

        isMine(message) {
            return message.user.id === this.currentUser.id
        },

        senderName(message) {
            if (this.isMine(message)) {
                return this.currentUser.profile.name;
            } else {
                if (this.anonymousSender) {
                    return this.anonymousSender;
                } else {
                    return message.user.profile.name;
                }
            }
        },

        startAutoRefresh() {
            this.updateAutoRefreshInterval();

            const textarea = this.$refs.textarea;
            if (textarea) {
                textarea.addEventListener('focus', this.handleTextareaFocus);
                textarea.addEventListener('blur', this.handleTextareaBlur);
            }
        },

        clearAutoRefresh() {
            if (this.autoRefreshInterval) {
                clearInterval(this.autoRefreshInterval);
                this.autoRefreshInterval = null;
            }

            const textarea = this.$refs.textarea;
            if (textarea) {
                textarea.removeEventListener('focus', this.handleTextareaFocus);
                textarea.removeEventListener('blur', this.handleTextareaBlur);
            }
        },

        updateAutoRefreshInterval() {
            this.clearAutoRefresh();

            const intervalTime = this.currentTextareaFocus ? 10000 : 30000;
            this.autoRefreshInterval = setInterval(() => {
                this.fetchNewMessages();
            }, intervalTime);
        },

        handleTextareaFocus() {
            this.currentTextareaFocus = true;
            this.updateAutoRefreshInterval();
        },

        handleTextareaBlur() {
            this.currentTextareaFocus = false;
            this.updateAutoRefreshInterval();
        },

        verifyState(status) {
            switch (status) {
                case "2":
                    return 'Negado';
                case "3":
                    return 'Indeferido';
                case "10":
                    return 'Deferido';
                default:
                    return '';
            }
        },

        handleEntitiesUpdate(entities) {
            this.chatEntities = entities; 
        },
    }
});
